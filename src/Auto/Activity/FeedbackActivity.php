<?php
/**
 * Feedback activity class
 * @package    Activity
 * @subpackage Feedback
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

use Auto\Activity\Activity;

/**
 * Feedback activity class overrides just post function.
 */
class FeedbackActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'feedback';

    /**
     * Post to the feedback activity.  Posting does the following:
     * <ul>
     * <li>Find all input fields questions and add a random sentence in response</li>
     * <li>Find all text areas questions and add a random paragrah of text</li>
     * <li>Find all multiple choice questions and randomly select a choice</li>
     * </ul>
     */
    public function post() {
        if ($element = $this->container->page->findLink('Answer the questions...')) {
            $element->click();
            $this->container->reloadPage($this->title);
            $textfields = $this->container->page->findAll('css', '.feedback_item_textfield input');
            foreach ($textfields as $textfield) {
                $textfield->setValue($this->container->contentHelper->getRandSentence());
            }
            $textareas = $this->container->page->findAll('css', '.feedback_item_textarea textarea');
            foreach ($textareas as $textarea) {
                if ($textarea->isVisible()) {
                    $textarea->setValue($this->container->contentHelper->getRandParagraph());
                } else {
                    $this->container->logHelper->failure($this->title . ': feedback_item_textfield textarea is not visible');
                }
            }
            $choices = $this->container->page->findAll('css', '.feedback_item_presentation_left ul');
            foreach ($choices as $choice) {
                if ($radiobtns = $choice->findAll('css', 'li input')) {
                    $radiobtns[rand(0, (count($radiobtns) - 1))]->click();
                }

            }
            if ($button = $this->container->page->findButton('Submit your answers')) {
                $button->click();
                $this->container->reloadPage($this->title);
            }
            if ($button = $this->container->page->findButton('Continue')) {
                $button->click();
                $this->container->reloadPage($this->title);
            }
        }
    }
}