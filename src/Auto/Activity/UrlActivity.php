<?php
/**
 * Url activity class
 * @package    Activity
 * @subpackage Url
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

use Auto\Activity\Activity;

/**
 * Url activity class. Extends the activity and does everything by default.
 */
class UrlActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'url';

    /**
     * Add values to the required fields for the url resource type in the resource creation screen
     */
    public function fillOutRequiredFields() {
        $field = $this->container->page->findField('externalurl');
        $field->setValue('http://www.moodlerooms.com');
    }
}