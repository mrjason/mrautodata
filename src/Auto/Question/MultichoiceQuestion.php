<?php
/**
 * Multichoice question type class
 * @package    Question
 * @subpackage Multichoice
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Question;

use Auto\Question\Question;

/**
 * Multichoice question type class.
 */
class MultichoiceQuestion extends Question {

    /**
     * @var string The type of question.
     */
    protected $type = 'multichoice';

    /**
     * Answer the question with a random paragraph. 10% of the time try to add a file if the filepicker is available..
     */
    public function answer() {
        $this->answerSetup();

        foreach ($this->field as $field) {
            if (substr($field->getText(), 3) == $this->answer) {
                $this->container->logHelper->action($this->title . ': Correctly answering with answer having value ' . $this->answer);
                $field->click();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAnswerField() {
        if ($fields = $this->qdiv->findAll('css', '.answer label')) {
            $this->field = $fields;
            return $this->field;
        }
        return false;
    }

    public function getRandomAnswer(){
        $this->answer = rand(1, count($this->field) - 1);
        $this->container->logHelper->action($this->title . ': Created random answer ' . $this->answer);
    }
}
