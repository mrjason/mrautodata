<?php
/**
 * Resource activity class
 * @package    Activity
 * @subpackage Resource
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

use Auto\Activity\Activity;

/**
 * Resource activity class. Extends the activity and does everything by default.
 */
class ResourceActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'resource';

    /**
     * Add a file to the file resource in the creation screen
     */
    public function fillOutRequiredFields() {
        $this->c->ch->uploadRandFile($this->c->cf->filedir, 'math', 'pdf');
    }
}