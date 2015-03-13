<?php
/**
 * Assignment for Moodle 2.2 class
 * @package    Activity
 * @subpackage Assignment
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

/**
 * Assignment for Moodle 2.2 class
 */
class AssignmentActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'assignment';

    /**
     * Post to the activity.  Perform the following:
     * <ul>
     * <li>Find the correct activity type by the button that is dislayed</li>
     * <li>Post content or upload a file</li>
     * <li>Send for marking if that is enabled</li>
     * </ul>
     */
    public function post() {
        $this->view();
        if ($button = $this->container->page->findButton('Add submission')) { /// online assignment
            $this->onlineText($button);
        } else if ($button = $this->container->page->findButton('Edit my submission')) { /// online edit assignment
            $this->onlineText($button);
        } else if ($button = $this->container->page->findButton('Upload a file')) { /// single file upload
            $button->click();
            $this->container->reloadPage($this->title);
            $element = $this->container->page->findButton('Choose a file...');
            $element->click();
            $this->container->contentHelper->uploadRandFile($this->container->cfg->filedir, 'math', 'pdf');
            $save = $this->container->page->findButton('Save changes');
            $save->press();
            $this->container->reloadPage($this->title); // single file upload has a continue screen we need to wait for reload on this.
            if ($continue = $this->container->page->findButton('Continue')) {
                $continue->press();
                $this->container->reloadPage($this->title);
            }
        } else if ($button = $this->container->page->findButton('Upload files')) { /// advanced file upload
            $this->advancedFile($button);
        } else if ($button = $this->container->page->findButton('Edit these files')) { /// advanced file upload second submission
            $this->advancedFile($button);
        }
        $this->container->reloadPage($this->title);

        if ($sendBtn = $this->container->page->findButton('Send for marking')) {
            $sendBtn->click();
            $this->container->reloadPage($this->title);
            $continue = $this->container->page->findButton('Continue');
            $continue->click();
            $this->container->reloadPage($this->title);
        }
    }

    /**
     * Post online text
     *
     * @param \Behat\Mink\NodeElement $button the \Behat\Mink\NodeElement for the button to click
     */
    private function onlineText($button) {
        $button->click();
        $this->container->reloadPage($this->title);
        if ($editor = $this->container->page->findField('id_text_editor')) {
            if ($editor->isVisible()) {
                $editor->setValue($this->container->contentHelper->getRandEssay('html'));
            } else {
                $this->container->logHelper->failure($this->title . ': id_texteditor textarea is not visible');
            }
        }
        if ($submit = $this->container->page->findButton('id_submitbutton')) {
            $submit->click();
        }
        $this->container->reloadPage($this->title);
    }

    /**
     * Upload a file to an advanced file type
     *
     * @param \Behat\Mink\NodeElement $button the \Behat\Mink\NodeElement for the button to click
     */
    private function advancedFile($button) {
        $button->click();
        $this->container->reloadPage($this->title);
        $this->container->contentHelper->uploadRandFile($this->container->cfg->filedir, 'math', 'pdf');
        if ($save = $this->container->page->findButton('Save changes')) {
            $save->press();
        }
        $this->container->reloadPage($this->title);
    }

    /**
     * This function is used to grade all users assignments in a course.  This doesn't check if a user actually submitted an assignment before grading the assignment.
     * @author Jason Hardin <jason@moodlerooms.com>
     */
    /*public function grade() {
        $this->container->reloadPage($this->title);
        if ($element = $this->container->page->find('css', 'div.reportlink > a')) {
            $element->click();
            $this->container->reloadPage($this->title);
        }

        if (!$button = $this->container->page->findLink('Update')) {
            $button = $this->container->page->findLink('Grade');
        }
        $button->click();
        $this->container->reloadPage($this->title);

        $next = true;
        $this->container->logHelper->action($this->title . ': Grading ' . $this->title);
        while ($next) {
            if ($element = $this->container->page->find('css', 'div#rubric-advancedgrading')) { /// Rubric grading
                $cXpath   = "//table[@id='advancedgrading-criteria']/tbody/tr";
                $criteria = $this->selenium->getXpathCount($cXpath);
                for ($c = 1; $c <= $criteria; $c++) {
                    $lXpath = $cXpath . "[$c]/td[@class='levels']/table/tbody/tr/td";
                    $levels = $this->selenium->getXpathCount($lXpath);
                    $click  = $lXpath . '[' . rand(1, $levels) . ']';
                    $this->container->logHelper->action($this->title . ': clicking criteria level ' . $element->getText()); //($click.'/'));
                    $element->click($click);
                }

                if (rand(0, 1)) {
                    $this->selenium->type("//textarea[@id='id_submissioncomment_editor']", $this->container->contentHelper->getRandParagraph());
                }
                if ($element = $this->container->page->find("//input[@value='Save and show next']")) {
                    $element->click("//input[@value='Save and show next']");
                    $this->container->reloadPage($this->title);
                } else {
                    $next   = false;
                    $button = $this->container->page->findButton('Save Changes');
                    $button->click();
                    $this->container->reloadPage($this->title);
                    break;
                }
            } else if ($element = $this->container->page->find('css', 'div#checklist-advancedgrading')) { /// Checklist grading
                $gXpath = "//div[@id='advancedgrading-groups']/div";
                $groups = $this->selenium->getXpathCount($gXpath);
                print("there are $groups groups\n");
                for ($g = 1; $g <= $groups; $g++) {
                    $iXpath = $gXpath . "[$g]/div[@class='items']/div/div/div";
                    $items  = $this->selenium->getXpathCount($iXpath);
                    for ($i = 1; $i <= $items; $i++) {
                        if (rand(0, 1)) {
                            $click = $iXpath . '[' . $i . ']';
                            $this->container->logHelper->action($this->title . ': clicking group item ' . $element->getText()); //($click."/div[@class='definition']"));
                            $element->click($click);
                            if (rand(0, 1) && $element = $this->container->page->find($click . "/div[@class='remark']/textarea")) {
                                $this->selenium->type($click . "/div[@class='remark']/textarea", $this->container->contentHelper->getRandParagraph());
                            }
                        }
                    }
                    if (rand(0, 1)) {
                        $this->selenium->type($click . "/div[@class='remark']/textarea", $this->container->contentHelper->getRandParagraph());
                    }
                }

                if ($element = $this->container->page->find("//input[@value='Save and show next']")) {
                    $element->click("//input[@value='Save and show next']");
                    $this->container->reloadPage($this->title);
                } else {
                    $next   = false;
                    $button = $this->container->page->findButton('Save Changes');
                    $button->click();
                    $this->container->reloadPage($this->title);
                    break;
                }
            } else {
                $baseXpath = "//fieldset[@id='Grades']/div[2]/";
                $selects   = $this->selenium->getXpathCount($baseXpath . 'div'); /// last div is current grade
                for ($i = 1; $i < $selects; $i++) {
                    $selectXpath = $baseXpath . "div[$i]/div[2]/select";
                    if ($element = $this->container->page->find($selectXpath)) {
                        $options = $this->selenium->getSelectOptions($selectXpath);
                        $option  = $options[rand(1, count($options) - 1)];
                        $this->container->logHelper->action($this->title . ': Selecting ' . $option);
                        $this->selenium->select($selectXpath, "label=$option");
                    }
                }

                $this->selenium->type("//textarea[@id='id_submissioncomment_editor']", $this->container->contentHelper->getRandParagraph());

                if ($element = $this->container->page->find("//input[@value='Save and show next']")) {
                    $element->click("//input[@value='Save and show next']");
                    $this->container->reloadPage($this->title);
                } else {
                    $next   = false;
                    $button = $this->container->page->findButton('Save Changes');
                    $button->click();
                    $this->container->reloadPage($this->title);
                    break;
                }
            }
        }
    }*/

}