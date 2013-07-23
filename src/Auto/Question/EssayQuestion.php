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
            $this->getField();
        }

        if (isset($this->field)) {
            $text = $this->c->ch->getRandParagraph();
            $this->c->l->action($this->title . ': Answering with answer ' . $text);
            $this->field->setValue($text);
            if (rand(0, 10) == 0) {
                $this->c->ch->uploadRandFile($this->c->cf->filedir, 'math', 'pdf');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getField() {
        if ($field = $this->qdiv->find('css', '.answer textarea')) {
            if ($field->isVisible()) {
                $this->field = $field;
                return $this->field;
            } else {
                $this->c->l->failure($this->title . ': Answer textarea is not visible');
            }
        } else {
            $this->c->l->action($this->title . ': Could not find answer field');
        }
        return false;
    }
}
