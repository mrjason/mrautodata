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
    protected $addactivityAttr = 'id';

    /**
     * This method returns all activities in the current course.
     * @return array an array of activity objects
     */
    public function getActivities() {
        /// If this is a folder view formatted course we might be in single folder view and want to get out of that
        if ($this->container->session->getCurrentUrl() != $this->url) {
            $this->container->visit($this->url);
        }
        if ($element = $this->container->page->find('css', 'div.topiclistlink > a')) {
            $element->click();
            $this->container->reloadPage($this->fullname);
        }

        if ($expand = $this->container->page->findLink('Expand All')) {
            $expand->click();
            $this->container->reloadPage($this->fullname);
        }

        $activitiesLi = $this->container->page->findAll('css', 'li.activity');
        $activities   = array();
        foreach ($activitiesLi as $li) {
            $classes   = explode(' ', $li->getAttribute('class'));
            $classname = '\Auto\Activity\\' . ucfirst($classes[1] . 'Activity');

            if (class_exists($classname)) {
                if ($link = $li->find('css', 'a')) {
                    $cssid = $li->getAttribute('id');
                    list($module, $id) = explode('-', $cssid);

                    $options      = array(
                        'container' => $this->container,
                        'cssid'     => $cssid,
                        'id'        => $id,
                        'title'     => $link->getText(),
                        'url'       => $link->getAttribute('href')
                    );
                    $activities[] = new $classname($options);
                }
            } else {
                //$this->log->add('Could not find classname '.$classname);
            }
        }

        return $activities;
    }

    /**
     * Click the add activity link in the course when editing is on
     */
    public function clickAddActivity() {
        $this->turnEditingOn();

        if ($addActivity = $this->container->page->findLink('Add Resource')) {
            $addActivity->click();
        } else {
            $this->container->logHelper->error($this->fullname . ': Could not find the add activity link');
        }
    }

    /**
     * Locate the activity creation node on the page or return false if it cannot be found.
     *
     * @param $activity
     *
     * @return bool|\Behat\Mink\NodeElement The activity creation node on the page or false if it is not found
     */
    public function getCreateAcitivty($activity) {
        if ($element = $this->container->page->find('css', 'div#' . $activity->getCssId() . ' a')) {
            return $element;
        }
        return false;
    }
}