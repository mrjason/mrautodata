<?php
/**
 * Calculated question type class.
 * @package    Question
 * @subpackage Calculated
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Question;

use Auto\Question\CalculatedsimpleQuestion;

/**
 * Calculated question type class.
 */
class CalculatedQuestion extends CalculatedsimpleQuestion {

    /**
     * @var string The type of question.
     */
    protected $type = 'calculated';

}