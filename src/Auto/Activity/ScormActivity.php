<?php
/**
 * Scorm activity class
 * @package    Activity
 * @subpackage Scorm
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */

namespace Auto\Activity;

/**
 * Scorm activity class. Extends the activity and does everything by default.
 */
class ScormActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'scorm';

    /**
     * Add the scorm file to the scorm creation screen
     */
    public function fillOutRequiredFields() {
        $this->container->contentHelper->uploadRandFile($this->container->cfg->filedir, 'scorm', 'zip');
    }
}