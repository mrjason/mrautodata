<?php
/**
 * Match question type class.
 * @package    Question
 * @subpackage Match
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Question;

use Auto\Question\Question;

/**
 * Short answer question type class.
 */
class MatchQuestion extends Question {

    /**
     * @var string The type of question.
     */
    protected $type = 'match';

    /**
     * Answer the question with the answer if it is set and we are within the range to correctly answer the question.
     * If the answer isn't set or we are not in the grade range add a random sentence to the field.
     */
    public function answer() {
        $this->answerSetup();

        /// If someone didn't create answers or messed up the number of answers then we generate random answers
        if (count($this->field) == count($this->answer)) {
            for ($i = 0; $i < count($this->field); $i++) {
                $options = $this->field[$i]->findAll('css', 'option');
                for ($j = 0; $j < count($options); $j++) {
                    $text = $options[$j]->getText();
                    if ($text == $this->answer[$i]) {
                        $this->container->logHelper->action($this->title . ': Correctly answering with answer ' . $this->answer[$i] . ' value is '
                        . $j);
                        $this->field[$i]->selectOption($j);
                    }
            }
            }
        } else {
            $this->answerRandomly();
        }
    }

    /**
     * Loop through each field, find the options in the select and then text if the text for the option matches the answer
     * and if so select that value
     *
     * @author Jason Hardin <jason@moodlerooms.com>
     */
    public function answerRandomly(){
        for ($i = 0; $i < count($this->field); $i++) {
            foreach ($this->field as $field) {
                $options  = $field->findAll('css', 'option');
                $rand     = rand(0, count($options) - 1);
                $selected = $options[$rand]->getValue();
                $text     = $options[$rand]->getText();
                $this->container->logHelper->action($this->title . ': Randomly answering with answer ' . $text . ' value is ' . $selected);
                $field->selectOption($selected);
            }
        }
    }

    /**
     * Locate all of the select fields in the question and return an array of \Behat\Mink\NodeElement objects.  Return false if nothing is found.
     * @return bool Any array of \Behat\Mink\NodeElement objects or false
     */
    public function getAnswerField() {
        if ($fields = $this->qdiv->findAll('css', '.answer .control select')) {
            $this->field = $fields;
            return true;
        }
        return false;
    }

    /**
     * Get all answers for all of the matching select fields.  Each select question should have an answer in it.
     * @return bool Any array of \Behat\Mink\NodeElement objects or false
     */
    public function getAnswer() {
        $this->answer = array();
        if ($answers = $this->qdiv->findAll('css', '.queanswer')) {
            foreach ($answers as $answer) {
                $this->answer[] = $answer->getText();
                $this->container->logHelper->action($this->title . ': Found queanswer set to ' . $answer->getText());
            }

            return true;
        }
        return false;
    }

}