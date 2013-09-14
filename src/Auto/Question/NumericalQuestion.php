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
        $this->answerSetup();
        if (isset($this->field)) {
            $this->container->logHelper->action($this->title . ': Answering with answer ' . $this->answer);
            $this->field->setValue($this->answer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAnswerField() {
        if ($field = $this->qdiv->find('css', '.answer input')) {
            $this->field = $field;
            return $this->field;
        } else {
            $this->container->logHelper->action($this->title . ': Could not find answer field');
        }
        return false;
    }

    public function getRandomAnswer(){
        $this->answer = (string)rand(0, 5);
        $this->container->logHelper->action($this->title . ': Creating random answer');
    }
}
