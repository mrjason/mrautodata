<?php
/**
 * Calculatedmulti question type class
 * @package    Question
 * @subpackage Calculatedmulti
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Question;

use Auto\Question\MultichoicQuestion;

/**
 * Calculatedmulti question type class.
 */
class CalculatedmultiQuestion extends MultichoiceQuestion {

    /**
     * @var string The type of question.
     */
    protected $type = 'calculatedmulti';
}
