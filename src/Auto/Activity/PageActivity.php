<?php
/**
 * Page activity class
 * @package    Activity
 * @subpackage Page
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */

namespace Auto\Activity;

use Auto\Activity\Activity;

/**
 * Page activity class. Extends the activity and does everything by default.
 */
class PageActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'page';

    /**
     * Add the required fields for the page activity type in the resource creation screen
     */
    public function fillOutRequiredFields() {
        $field = $this->c->p->findField('id_page');
        $field->setValue($this->c->ch->getRandParagraph());
    }
}