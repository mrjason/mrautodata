<?php
/**
 * Advanced forum or HSU forum activity class
 *
 * @package Activity
 * @subpackage Hsuforum
 * @author Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

use Auto\Activity\ForumActivity;

/**
 * Hsuforum activity class
 */
class HsuforumActivity extends ForumActivity {

    /**
     * @var string The activity type.
     */
    protected $type = 'hsuforum';
}