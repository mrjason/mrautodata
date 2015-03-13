<?php
/**
 * Course class
 * @package   Course
 * @author    Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */

namespace Auto\Course;

/**
 * Base course class for interacting with a course in Moodle.
 */
class WeeksCourse extends Course {

    /**
     * @var string The course format.
     */
    protected $format = 'weeks';
}