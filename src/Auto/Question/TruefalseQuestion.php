<?php
/**
 * Truefalse question type class
 *
 * @package Question
 * @subpackage Truefalse
 * @author Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Question;

use Auto\Question\Question;

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
    public function answer(){
        if(!isset($this->field)){
            $this->getField();
        }

        foreach($this->field as $field){
            if($field->getAttribute('value') == $this->answer){
                $this->c->l->action('Answering '.$this->title.' with answer with value '.$this->answer);
                $field->click();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getField(){
        if($fields = $this->qdiv->findAll('css','.answer input')){
            $this->field = $fields;
            return $this->field;
        }
        return false;
    }

    /**
     * Get the answer value that has been added to the question text or return a randomg number between 0 and 1 if it can't be found.
     *
     * @return string Locate the answer value in the question and set that in the object and return it.
     */
    public function getAnswer(){
        if($answer = $this->qdiv->find('css','.mrqueanswer')){
            $this->answer = $answer->getText();
            if($this->answer == 'true'){
                $this->answer = '1';
            } else {
                $this->answer = '0';
            }
            $this->c->l->action($this->title.': Found mrqueanswer set to '.$this->answer);
        } else {
            $this->answer = rand(0,1);
            $this->c->l->action($this->title.': Created random answer '.$this->answer);
        }
        return $this->answer;
    }
}
