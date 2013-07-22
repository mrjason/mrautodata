<?php
/**
 * Imscp activity class
 *
 * @package Activity
 * @subpackage Imscp
 * @author Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */

namespace Auto\Activity;

use Auto\Activity\Activity;

/**
 * Imscp activity class. Extends the activity and does everything by default.
 */
class ImscpActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'imscp';

    /**
     * Add the imscp file to the imscp creation screen
     */
    public function fillOutRequiredFields(){
        $this->c->ch->uploadRandFile($this->c->cf->filedir,'imscp','zip');
    }
}