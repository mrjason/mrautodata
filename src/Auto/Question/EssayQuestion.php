<?php
/**
 * Essay question type class
 * @package    Question
 * @subpackage Essay
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Question;

use Auto\Question\Question;

/**
 * Essay question type class.
 */
class EssayQuestion extends Question {

    /**
     * @var string The type of question.
     */
    protected $type = 'essay';

    /**
     * Answer the question with a random paragraph. 10% of the time try to add a file if the filepicker is available..
     */
    public function answer() {
        if (!isset($this->field)) {
            $this->getAnswerField();
        }

        if (isset($this->field)) {
            $text = $this->container->contentHelper->getRandParagraph();
            $this->container->logHelper->action($this->title . ': Answering with answer ' . $text);
            $this->field->setValue($text);
            if (rand(0, 10) == 0) {
                $this->container->contentHelper->uploadRandFile($this->container->cfg->filedir, 'math', 'pdf');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAnswerField() {
        if ($field = $this->qdiv->find('css', '.answer textarea')) {
            if ($field->isVisible()) {
                $this->field = $field;
                return $this->field;
            } else {
                $this->container->logHelper->failure($this->title . ': Answer textarea is not visible');
            }
        } else {
            $this->container->logHelper->action($this->title . ': Could not find answer field');
        }
        return false;
    }
}
