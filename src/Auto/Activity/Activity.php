<?php

/**
 * Activity base class
 * @package   Activity
 * @author    Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */

namespace Auto\Activity;

/**
 * Base class for Moodle activities.  With the base functions that a class will need.
 */
class Activity {

    /**
     * @var string the activity's id used in the Moodle database in in most links to the activity.
     */
    protected $id;
    /**
     * @var string the value for the id attribute for the link in the course
     */
    protected $courseLinkId;
    /**
     * @var string the title of the activity
     */
    protected $title;
    /**
     * @var string the type of activity
     */
    protected $type;
    /**
     * @var string url to the activity
     */
    protected $url;

    /**
     * @var Container containing all variables for the session, page, log helper and content helper.
     */
    protected $container;

    /**
     * Constructor for the class
     *
     * @param $options mixed And array of variables that will be set for the activity.  Must include the Container c variable to work.  Array should be array('variable name'=> $value).
     */
    public function __construct($options) {
        foreach ($options as $name => $value) {
            $this->{$name} = $value;
        }
        $this->container->reloadPage($this->title);
    }

    /**
     * Create the activity by filling out the activity create form.
     */
    public function create() {
        $this->fillOutCreateForm();
        $this->fillOutRequiredFields();
        if ($save = $this->container->page->findButton('Save and return to course')) {
            $this->container->logHelper->action($this->title . ': Saving ' . $this->type . ' and returning to the course.');
            $save->press();
            $this->container->reloadPage($this->title);
            if ($error = $this->container->page->find('css', '.error')) {
                $this->container->logHelper->error($this->title . ': Error in create activity form for ' . $this->type . '. Error is ' . $error->getText());
            }
        }
    }

    /**
     * Grade the activity. This should be overridden by the activity.
     *
     * @param $grade The grade the student should receive
     */
    public function grade($grade) {
    }

    /**
     *  Interact with the activity, usually view and post. THis should be overridden by the activity unless all that the activity needs is to be viewed.
     */
    public function interact() {
        $this->container->logHelper->action($this->title . ': Starting interaction');
        $this->post();
    }

    public function teacherInteract($grade){
        /// Usually the teacher does nothing unless overridden
    }

    /**
     * View the activity and create content. This should be overridden by the activity unless all it needs to do is be viewed.
     */
    public function post() {
        $this->view();
    }

    /**
     * View the activity.
     */
    public function view() {
        $this->container->reloadPage($this->title);
        $this->access('course');
        $this->container->logHelper->action($this->title . ': Viewing');
        $this->container->reloadPage($this->title);
    }

    /**
     * Mark the activity as completed or not completed by clicking on the activity completion icon if it exists.
     */
    public function complete() {
        if ($li = $this->getCourseLink()) {
            if ($element = $li->find('css', 'input[type="image"]')) {
                $this->container->logHelper->action($this->title . ': Marking complete');
                $element->click();
                $this->container->reloadPage($this->title);
            }
        }
    }

    /**
     * Access the activity based on the location that is being requested.  Locations are the course or the navigation bar.
     *
     * @param string $type
     */
    public function access($type = '') {
        switch ($type) {
            case 'nav':
                $link    = $this->getNavLink();
                $logarea = 'navigation bar';
                break;
            case 'course':
                $link    = $this->getCourseLink();
                $logarea = 'course section';
                break;
        }
        if (!empty($link)) {
            $this->container->logHelper->action($this->title . ': Clicked on activity ' . $logarea . ' link');
            $link->click();
            $this->container->reloadPage($this->title);
        } else if (isset($this->url)) {
            $this->container->logHelper->action($this->title . ': Returning to activity using url ' . $this->url);
            $this->container->visit($this->url);
        } else {
            $this->container->logHelper->action($this->title . ': Could not find link to ' . $logarea . ' and the url was not set');
        }
    }

    /**
     * Add values to the general fields in the create activity form.
     */
    public function fillOutCreateForm() {
        if ($field = $this->container->page->findField('name')) {
            $field->setValue($this->title);

        } else if ($field = $this->container->page->findField('title')) {
            $field->setValue(ucfirst($this->type) . ' Activity Auto Created');
        }
        if ($div = $this->container->page->find('css', '#fitem_id_introeditor')) {
            $class = $div->getAttribute('class');
            if (strpos($class, 'hide') === false) { // Some activities hide the introeditor to not require that field be entered, External tools specifically.
                $field = $this->container->page->findField('id_introeditor');
                $field->setValue($this->container->contentHelper->getRandParagraph());
            }
        } else if ($field = $this->container->page->findField('description')) {
            $field->setValue($this->container->contentHelper->getRandParagraph());
        }
        $this->fillOutRequiredFields();
    }

    /**
     * Add values to the required field for the activity.  This is not used if the activity has no required fields.
     */
    protected function fillOutRequiredFields() {
    }

    /**
     * Locate the activity link in the navigation bar.
     * @return bool Return the link object or false if the link can't be found.
     */
    public function getNavLink() {
        if ($navbar = $this->container->page->find('css', '.navbar')) {
            if ($link = $this->container->page->findLink($this->title)) {
                return $link;
            }
        }

        return false;
    }

    /**
     * Locate the link to the activity in the course.
     * @return bool Return the link object or false if the link doesn't exist on the page.
     */
    public function getCourseLink() {
        if ($li = $this->container->page->find('css', '#' . $this->courseLinkId)) {
            if ($link = $li->findLink($this->title)) {
                return $link;
            }
        }
        return false;
    }

    /**
     * Set the activity's id.
     *
     * @param string $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Return the current id for the activity
     * @return string
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the CSS id for the link to the activity in the course.
     *
     * @param string $cssId
     */
    public function setCssId($cssId) {
        $this->courseLinkId = $cssId;
    }

    /**
     * Returns the CSS id for the link to the activity in the course.
     * @return string The id attribute for the course link to the activity
     */
    public function getCssId() {
        return $this->courseLinkId;
    }

    /**
     * Set the title of the activity
     *
     * @param string $title The title of the activity to be set.
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Return the title of the activity
     * @return string The title of the activity
     */
    public function getTitle() {
        return $this->title;
    }

    public function getType(){
        return $this->type;
    }
}