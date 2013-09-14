<?php
/**
 * Shortanswer question type class.
 * @package    Question
 * @subpackage Shortanswer
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Question;

use Auto\Question\Question;

/**
 * Short answer question type class.
 */
class ShortanswerQuestion extends Question {

    /**
     * @var string The type of question.
     */
    protected $type = 'shortanswer';

    /**
     * Answer the question with the answer if it is set and we are within the range to correctly answer the question.
     * If the answer isn't set or we are not in the grade range add a random sentence to the field.
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
        $this->answer = $this->container->contentHelper->getRandSentence();
        $this->container->logHelper->action($this->title . ': Created random answer ' . $this->answer);
    }

}