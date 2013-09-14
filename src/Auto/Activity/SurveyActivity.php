<?php
/**
 * Survey activity class
 * @package    Activity
 * @subpackage Survey
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */

namespace Auto\Activity;

use Auto\Activity\Activity;

/**
 * Survey activity class. Extends the activity and does everything by default.
 */
class SurveyActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'survey';

    /**
     * Add the survey type required field to the survey creation screen.
     */
    public function fillOutRequiredFields() {
        $select = $this->container->page->findField('template');
        $select->selectOption(rand(1, 5));
    }
}