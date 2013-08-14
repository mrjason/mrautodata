<?php
/**
 * Choice activity class
 * @package    Activity
 * @subpackage Choice
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

use Auto\Activity\Activity;

/**
 * Choice activity class
 */
class ChoiceActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'choice';

    /**
     * View the choice activity and then randomly select a choice
     */
    public function post() {
        $this->view();
        if ($save = $this->c->p->findButton('Save my choice')) {
            if ($answers = $this->c->p->findAll('named', array('radio', 'answer'))) {
                $answers[rand(0, (count($answers) - 1))]->click();
            }
            $save->press();
            $this->c->reloadPage($this->title);
        }
    }

    /**
     * Add content to the 5 choice fields that are required fields.
     */
    public function fillOutRequiredFields() {
        for ($i = 0; $i < 5; $i++) {
            $field = $this->c->p->findField('id_option_' . $i);
            $field->setValue($this->c->ch->getRandWord());
        }
    }
}