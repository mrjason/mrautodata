<?php
/**
 * Truefalse question type class
 * @package    Question
 * @subpackage Truefalse
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Question;

/**
 * True False question type class.
 */
class TruefalseQuestion extends Question {

    /**
     * @var string The type of question.
     */
    protected $type = 'truefalse';

    /**
     * Answer the question with a random paragraph. 10% of the time try to add a file if the filepicker is available..
     */
    public function answer() {
        $this->answerSetup();

        if ($this->answer == 'true') {
            $this->answer = '1';
        } else {
            $this->answer = '0';
        }

        foreach ($this->field as $field) {
            if ($field->getAttribute('value') == $this->answer) {
                $this->container->logHelper->action($this->title . ': Answering with answer with value ' . $this->answer);
                $field->click();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAnswerField() {
        if ($fields = $this->qdiv->findAll('css', '.answer input')) {
            $this->field = $fields;
            return $this->field;
        }
        return false;
    }

    public function getRandomAnswer() {
        $this->answer = rand(0, 1);
        $this->container->logHelper->action($this->title . ': Created random answer ' . $this->answer);
    }
}
