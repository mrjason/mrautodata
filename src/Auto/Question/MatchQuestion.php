<?php
/**
 * Match question type class.
 *
 * @package Question
 * @subpackage Match
 * @author Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
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
    public function answer(){
        if(!isset($this->field)){
            $this->getField();
        }

    /// If someone messed up setting up the Matching and we have more or fewer answers than they are questions then we generate random answers
        if(count($this->field) != count($this->answer)){
            foreach($this->field as $field){
                $options = $field->findAll('css','option');
                $rand = rand(0,count($options)-1);
                $selected  = $options[$rand]->getValue();
                $text = $options[$rand]->getText();
                $this->c->l->action($this->title.': Answering with answer '.$text . ' value is '.$selected);
                $field->selectOption($selected);
            }
        } else {
        /// Loop through each field, find the options in the select and then text if the text for the option matches the answer
        /// and if so select that value
            for($i = 0; $i < count($this->field); $i++){
                $options = $this->field[$i]->findAll('css','option');
                for($j=0; $j < count($options); $j++){
                    $text = $options[$j]->getText();
                    if($text == $this->answer[$i]){
                        $this->c->l->action($this->title.': Answering with answer '.$this->answer[$i]. ' value is '.$j);
                        $this->field[$i]->selectOption($j);
                    }
                }

            }
        }
    }

    /**
     * Locate all of the select fields in the question and return an array of \Behat\Mink\NodeElement objects.  Return false if nothing is found.
     * @return array|bool Any array of \Behat\Mink\NodeElement objects or false
     */
    public function getField(){
        if($fields = $this->qdiv->findAll('css','.answer .control select')){
            $this->field = $fields;
            return $this->field;
        }
        return false;
    }

    /**
     * Get all answers for all of the matching select fields.  Each select question should have an answer in it.
     *
     * @return array|bool Any array of \Behat\Mink\NodeElement objects or false
     */
    public function getAnswer(){
        if($answers = $this->qdiv->findAll('css','.mrqueanswer')){
            $this->answer = array();
            foreach($answers as $answer){
                $this->answer[] = $answer->getText();
                $this->c->l->action($this->title.': Found mrqueanswer set to '.$answer->getText());
            }

            return $this->answer;
        }
        return false;
    }

}