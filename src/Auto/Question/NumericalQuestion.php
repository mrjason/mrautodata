<?php
/**
 * Numerical question type class
 * @package    Question
 * @subpackage Numerical
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Question;

use Auto\Question\Question;

/**
 * Numerical question type class.
 */
class NumericalQuestion extends Question {

    /**
     * @var string The type of question.
     */
    protected $type = 'numerical';

    /**
     * Answer the question with the answer if it is set and we are withing the correct marking range.
     * If the answer isn't set then generate a random number between 1 and 5.
     */
    public function answer() {
        if (!isset($this->field)) {
            $this->getField();
        }
        if (isset($this->field)) {
            $this->c->l->action($this->title . ': Answering with answer ' . $this->answer);
            $this->field->setValue($this->answer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getField() {
        if ($field = $this->qdiv->find('css', '.answer input')) {
            $this->field = $field;
            return $this->field;
        } else {
            $this->c->l->action($this->title . ': Could not find answer field');
        }
        return false;
    }

    /**
     * Get the answer value that has been added to the question text or return generate a raond number between 0 and 5 if it can't be found.
     * @return string Locate the answer value in the question and set that in the object and return it.
     */
    public function getAnswer() {
        if ($answer = $this->qdiv->find('css', '.mrqueanswer')) {
            $this->answer = $answer->getText();
        } else {
            $this->answer = (string)rand(0, 5);
        }
        return $this->answer;
    }
}
