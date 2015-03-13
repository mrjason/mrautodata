<?php
/**
 * Calculatedsimple question type class.
 * @package    Question
 * @subpackage Calculatedsimple
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Question;

/**
 * Calculated Simple question type class.
 */
class CalculatedsimpleQuestion extends Question {

    /**
     * @var string The type of question.
     */
    protected $type = 'calculatedsimple';

    /**
     * Answer the question with the answer if it is set and we are withing the correct marking range.
     * If the answer isn't set then generate a random number between 1 and 5.
     */
    public function answer() {
        $this->answerSetup();

        if (isset($this->field)) {
            /// I want to replace this with Fabian's expression language https://github.com/symfony/symfony/pull/8913 whenever it
            /// is released
            eval('$answer = ' . $this->answer . ';');
            $this->container->logHelper->action($this->title . ': Answering with answer ' . $this->answer);
            $this->field->setValue((string)$answer);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAnswerField() {
        if ($field = $this->qdiv->find('css', '.answer input')) {
            $this->field = $field;
            return true;
        } else {
            $this->container->logHelper->action($this->title . ': Could not find answer field');
        }
        return false;
    }

    /**
     * Get the answer value that has been added to the question text or return generate a random number between 0 and 5 if it can't be found.
     * @return string The answer to the question.
     */
    public function getRandomAnswer() {
        $this->answer = (string)rand(0, 5);
        $this->container->logHelper->action($this->title . ': Created random answer ' . $this->answer);
    }
}