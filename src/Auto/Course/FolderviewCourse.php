<?php
/**
* Course class
*
* @package Course
* @author Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */

namespace Auto\Course;

use Auto\Course\Course;

/**
 * Base course class for interacting with a course in Moodle.
 */
class FolderviewCourse extends Course {

    /**
     * @var string The course format.
     */
    protected $format = 'folderview';

    /**
     * @var string The CSS attributes to locate an activity field in the add activity area of the course format
     */
    protected $addActivityCss = '#addResource div.column div';
    /**
     * @var string Attribute to identify the activity in the add activity area of the course format
     */
    protected $addactivityAttr ='id';

    /**
     * Click the add activity link in the course when editing is on
     */
    public function clickAddActivity(){
        $this->turnEditingOn();

        if($addActivity = $this->c->p->findLink('Add Resource')){
            $addActivity->click();
        } else {
            $this->c->l->error($this->fullname. ': Could not find the add activity link');
        }
    }

    /**
     * Locate the activity creation node on the page or return false if it cannot be found.
     *
     * @param $activity
     * @return bool|\Behat\Mink\NodeElement The activity creation node on the page or false if it is not found
     */
    public function getCreateAcitivty($activity){
        if($el = $this->c->p->find('css', 'div#'.$activity->getCssId().' a')){
            return $el;
        }
        return false;
    }
}