<?php
/**
 * Assignment for Moodle 2.2 class
 * @package Activity
 * @subpackage Assignment
 * @author Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

use Auto\Activity\Activity;

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
    public function post(){
        $this->view();
        if($btn = $this->c->p->findButton('Add submission')) {  /// online assignment
            $this->onlineText($btn);
        } else if($btn = $this->c->p->findButton('Edit my submission')){ /// online edit assignment
            $this->onlineText($btn);
        } else if($btn = $this->c->p->findButton('Upload a file')) {  /// single file upload
            $btn->click();
            $this->c->reloadPage();
            $el = $this->c->p->findButton('Choose a file...');
            $el->click();
            $this->c->ch->uploadRandFile($this->c->cf->filedir,'math','pdf');
            $save = $this->c->p->findButton('Save changes');
            $save->press();
            $this->c->reloadPage(); // single file upload has a continue screen we need to wait for reload on this.
            if($continue = $this->c->p->findButton('Continue')){
                $continue->press();
                $this->c->reloadPage();
            }
        } else if($btn = $this->c->p->findButton('Upload files')) {  /// advanced file upload
            $this->advancedFile($btn);
        } else if($btn = $this->c->p->findButton('Edit these files')) {  /// advanced file upload second submission
            $this->advancedFile($btn);
        }
        $this->c->reloadPage();

        if($sendBtn = $this->c->p->findButton('Send for marking')){
            $sendBtn->click();
            $this->c->reloadPage();
            $continue = $this->c->p->findButton('Continue');
            $continue->click();
            $this->c->reloadPage();
        }
    }

    /**
     * Post online text
     *
     * @param \Behat\Mink\NodeElement $btn the \Behat\Mink\NodeElement for the button to click
     */
    private function onlineText($btn){
        $btn->click();
        $this->c->reloadPage();
        if($editor = $this->c->p->findField('id_text_editor')){
            if($editor->isVisible()){
                $editor->setValue($this->c->ch->getRandEssay('html'));
            } else {
                $this->c->l->failure($this->title.': id_texteditor textarea is not visible');
            }
        }
        if($submit = $this->c->p->findButton('id_submitbutton')){
            $submit->click();
        }
        $this->c->reloadPage();
    }

    /**
     * Upload a file to an advanced file type
     *
     * @param \Behat\Mink\NodeElement $btn the \Behat\Mink\NodeElement for the button to click
     */
    private function advancedFile($btn){
        $btn->click();
        $this->c->reloadPage();
        $this->c->ch->uploadRandFile($this->c->cf->filedir,'math','pdf');
        if($save = $this->c->p->findButton('Save changes')){
            $save->press();
        }
        $this->c->reloadPage();
    }

    /**
     * This function is used to grade all users assignments in a course.  This doesn't check if a user actually submitted an assignment before grading the assignment.
     *
     * @todo Make this work correctly with Mink
     * @access public
     * @param int $activity The cmid of the assignment to have the discussion created in.
     */
    public function grade(){
        $this->c->reloadPage();
        if($el = $this->c->p->find('css','div.reportlink > a')){
            $el->click();
            $this->c->reloadPage();
        }

        if(!$btn = $this->c->p->findLink('Update')){
            $btn = $this->c->p->findLink('Grade');
        }
        $btn->click();
        $this->c->reloadPage();

        $next = true;
        $this->c->l->action($this->title.': Grading '.$this->title);
        while($next){
            if($el = $this->c->p->find('css','div#rubric-advancedgrading')){ /// Rubric grading
                $cXpath = "//table[@id='advancedgrading-criteria']/tbody/tr";
                $criteria = $this->selenium->getXpathCount($cXpath);
                for($c = 1; $c <= $criteria; $c++){
                    $lXpath = $cXpath."[$c]/td[@class='levels']/table/tbody/tr/td";
                    $levels = $this->selenium->getXpathCount($lXpath );
                    $click = $lXpath.'['.rand(1, $levels).']';
                    $this->c->l->action($this->title.': clicking criteria level '.$el->getText());//($click.'/'));
                    $el->click($click);
                }

                if(rand(0, 1)){
                    $this->selenium->type("//textarea[@id='id_submissioncomment_editor']", $this->c->ch->getRandParagraph());
                }
                if($el = $this->c->p->find("//input[@value='Save and show next']")){
                    $el->click("//input[@value='Save and show next']");
                    $this->c->reloadPage();
                } else {
                    $next = false;
                    $button = $this->c->p->findButton('Save Changes');
                    $button->click();
                    $this->c->reloadPage();
                    break;
                }
            } else if($el = $this->c->p->find('css','div#checklist-advancedgrading')){ /// Checklist grading
                $gXpath = "//div[@id='advancedgrading-groups']/div";
                $groups = $this->selenium->getXpathCount($gXpath);
                print("there are $groups groups\n");
                for($g = 1; $g <= $groups; $g++){
                    $iXpath = $gXpath."[$g]/div[@class='items']/div/div/div";
                    $items = $this->selenium->getXpathCount($iXpath );
                    for($i=1; $i <= $items; $i++){
                        if(rand(0, 1)){
                            $click = $iXpath.'['.$i.']';
                            $this->c->l->action($this->title.': clicking group item '.$el->getText());//($click."/div[@class='definition']"));
                            $el->click($click);
                            if(rand(0, 1) && $el = $this->c->p->find($click."/div[@class='remark']/textarea")){
                                $this->selenium->type($click."/div[@class='remark']/textarea", $this->c->ch->getRandParagraph());
                            }
                        }
                    }
                    if(rand(0, 1)){
                        $this->selenium->type($click."/div[@class='remark']/textarea", $this->c->ch->getRandParagraph());
                    }
                }

                if($el = $this->c->p->find("//input[@value='Save and show next']")){
                    $el->click("//input[@value='Save and show next']");
                    $this->c->reloadPage();
                } else {
                    $next = false;
                    $button = $this->c->p->findButton('Save Changes');
                    $button->click();
                    $this->c->reloadPage();
                    break;
                }
            } else {
                $baseXpath = "//fieldset[@id='Grades']/div[2]/";
                $selects = $this->selenium->getXpathCount($baseXpath.'div'); /// last div is current grade
                for($i = 1; $i < $selects; $i++){
                    $selectXpath = $baseXpath."div[$i]/div[2]/select";
                    if($el = $this->c->p->find($selectXpath)){
                        $options = $this->selenium->getSelectOptions($selectXpath);
                        $option = $options[rand(1,count($options)-1)];
                        $this->c->l->action("Selecting $option");
                        $this->selenium->select($selectXpath, "label=$option");
                    }
                }

                $this->selenium->type("//textarea[@id='id_submissioncomment_editor']", $this->c->ch->getRandParagraph());

                if($el = $this->c->p->find("//input[@value='Save and show next']")){
                    $el->click("//input[@value='Save and show next']");
                    $this->c->reloadPage();
                } else {
                    $next = false;
                    $button = $this->c->p->findButton('Save Changes');
                    $button->click();
                    $this->c->reloadPage();
                    break;
                }
            }
        }
    }

}