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
     * @var string the activity's id used int eh moodle database in in most links to the activity.
     */
    protected $id;
    /**
     * @var string the value for the id attribute for the link in the course
     */
    protected $cssid;
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
    protected $c;

    /**
     * Consturctor for the class
     *
     * @param $options mixed And array of variables that will be set for the activity.  Must include the Container c variable to work.  Array should be array('variable name'=> $value).
     */
    public function __construct($options) {
        foreach ($options as $name => $value) {
            $this->{$name} = $value;
        }
        $this->c->reloadPage();
    }

    /**
     * Create the activity by filling out the activity create form.
     */
    public function create() {
        $this->fillOutCreateForm();
        $this->fillOutRequiredFields();
        if ($save = $this->c->p->findButton('Save and return to course')) {
            $this->c->l->action($this->title . ': Saving ' . $this->type . ' and returning to the course.');
            $save->press();
            $this->c->reloadPage();
            if ($error = $this->c->p->find('css', '.error')) {
                $this->c->l->error($this->title . ': Error in create activity form for ' . $this->type . '. Error is ' . $error->getText());
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
        $this->c->l->action($this->title . ': Starting interaction');
        $this->post();
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
        $this->c->reloadPage();
        $this->access('course');
        $this->c->l->action($this->title . ': Viewing ' . $this->title . ' activity type ' . $this->type);
        $this->c->reloadPage();
    }

    /**
     * Mark the activity as completed or not completed by clicking on the activity completion icon if it exists.
     */
    public function complete() {
        if ($li = $this->getCourseLink()) {
            if ($el = $li->find('css', 'input[type="image"]')) {
                $this->c->l->action($this->title . ': Marking complete');
                $el->click();
                $this->c->reloadPage();
                $this->c->l->action($el->getAttribute('title'));
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
            $this->c->l->action($this->title . ': Clicked on activity ' . $logarea . ' link titled ' . $this->title . ' activity type ' . $this->type);
            $link->click();
            $this->c->reloadPage();
        } else if (isset($this->url)) {
            $this->c->l->action($this->title . ': Returning to activity ' . $this->title . ' with url ' . $this->url);
            $this->c->visit($this->url);
        } else {
            $this->c->l->action($this->title . ': Could not find link to ' . $logarea . ' and the url was not set for ' . $this->title);
        }
    }

    /**
     * Add values to the general fields in the create activity form.
     */
    public function fillOutCreateForm() {
        if ($field = $this->c->p->findField('name')) {
            $field->setValue($this->title);

        } else if ($field = $this->c->p->findField('title')) {
            $field->setValue(ucfirst($this->type) . ' Activity Auto Created');
        }
        if ($div = $this->c->p->find('css', '#fitem_id_introeditor')) {
            $class = $div->getAttribute('class');
            if (strpos($class, 'hide') === false) { // Some activities hide the introeditor to not require that field be entered, External tools specifically.
                $field = $this->c->p->findField('id_introeditor');
                $field->setValue($this->c->ch->getRandParagraph());
            }
        } else if ($field = $this->c->p->findField('description')) {
            $field->setValue($this->c->ch->getRandParagraph());
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
        if ($navbar = $this->c->p->find('css', '.navbar')) {
            if ($link = $this->c->p->findLink($this->title)) {
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
        if ($li = $this->c->p->find('css', '#' . $this->cssid)) {
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
     * @param string $cssid
     */
    public function setCssId($cssid) {
        $this->cssid = $cssid;
    }

    /**
     * Returns the CSS id for the link to the activity in the course.
     * @return string The id attribute for the course link to the activity
     */
    public function getCssId() {
        return $this->cssid;
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
}