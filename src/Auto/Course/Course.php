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
class Course {

    /**
     * @var string The course id that is used in the Moodle database and most urls.
     */
    protected $id;
    /**
     * @var string Course fullname
     */
    protected $fullname;
    /**
     * @var string course Shortname, not currently used
     */
    protected $shortname;
    /**
     * @var string Url for the course.  Used for direct access.
     */
    protected $url;
    /**
     * @var Container containing all variables for the session, page, log helper and content helper.
     */
    protected $c;

    /**
     * @var string The CSS attributes to locate an activity field in the add activity area of the course format
     */
    protected $addActivityCss = '#chooseform .options .option label';
    /**
     * @var string Attribute to identify the activity in the add activity area of the course format
     */
    protected $addactivityAttr = 'for';

    /**
     * Consturctor for the class
     *
     * @param $options mixed And array of variables that will be set for the course.  Must include the Container c variable to work.  Array should be array('variable name'=> $value).
     */
    public function __construct($options) {
        foreach ($options as $name => $value) {
            $this->{$name} = $value;
        }
        $this->c->reloadPage();
    }

    /**
     * This method returns all activities in the current course.
     * @return array an array of activity objects
     */
    public function getActivities() {
        /// If this is a folder view formated course we might be in single folder view and want to get out of that
        if ($this->c->s->getCurrentUrl() != $this->url) {
            $this->c->visit($this->url);
        }
        if ($el = $this->c->p->find('css', 'div.topiclistlink > a')) {
            $el->click();
            $this->c->reloadPage();
        }

        if ($expand = $this->c->p->findLink('Expand All')) {
            $expand->click();
            $this->c->reloadPage();
        }

        $activitiesLi = $this->c->p->findAll('css', 'li.activity');
        $activities   = array();
        foreach ($activitiesLi as $li) {
            $classes   = explode(' ', $li->getAttribute('class'));
            $classname = '\Auto\Activity\\' . ucfirst($classes[1] . 'Activity');

            if (class_exists($classname)) {
                if ($link = $li->find('css', 'a')) {
                    $cssid = $li->getAttribute('id');
                    list($module, $id) = explode('-', $cssid);

                    $options      = array(
                        'c'     => $this->c,
                        'cssid' => $cssid,
                        'id'    => $id,
                        'title' => $link->getText(),
                        'url'   => $link->getAttribute('href')
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
     * This function is used to get all sections of the current course and returns them as an array of section objects.
     * @return array an array of section objects.
     */
    public function getSections() {
        $this->c->l->action("Getting all course sections");
        $elSections = $this->c->p->findAll('css', 'div.course-content ul li');

        $sections = array();
        foreach ($elSections as $el) {
            $section     = new \stdClass;
            $section->id = $el->getAttribute('id');
            if ($elName = $this->c->p->find('css', 'h3.sectionname')) {
                $section->name = $elName->getText();
            } else {
                $section->name = '';
            }
            $sections[] = $section;
        }
        return $sections;
    }

    /**
     * This method clicks on the first link with the text for the full name of the course. If a full name link is not found then the course is accessed via the url
     * @access public
     */
    public function clickFullnameLink() {
        if ($el = $this->c->p->findLink($this->fullname)) {
            $this->c->l->action("Clicked on " . $this->fullname);
            $el->click();
            $this->c->reloadPage();
        } else {
            if ($this->c->cf->debug) {
                $this->c->l->action("Can't find current course link " . $this->fullname);
            }
            if (isset($this->url)) {
                $this->c->l->action('Returning to course the course ' . $this->fullname . ' via the url ' . $this->url);
                $this->c->visit($this->url);
            }
        }
    }

    /**
     * This method clicks on the course link in the navigation bar
     * @access public
     */
    public function clickNavLink() {
        if ($navbar = $this->c->p->find('css', '.navbar')) {
            if ($el = $navbar->findLink($this->fullname)) {
                $this->c->l->action('Clicked on Course Nav link ' . $this->fullname);
                $el->click();
                $this->c->reloadPage();
            }
        } else if (isset($this->url)) {
            $this->c->l->action('Returning to course' . $this->fullname . ' with url ' . $this->url);
            $this->c->visit($this->url);
        }
    }

    /**
     * Create a course based on the currently set course. If the course shortname exists increment until the course can be created.
     */
    public function create() {
        $i = 0;

        $this->c->visit('/course/index.php?categoryedit=on');
        $this->c->l->action('Adding course');
        if ($btn = $this->c->p->findButton('Add a new course')) {
            $btn->click();
            $this->c->reloadPage();
            do {
                $fullname  = $this->fullname . $i;
                $shortname = $this->shortname . $i;
                $elfn      = $this->c->p->findField('fullname');
                $elfn->setValue($fullname);
                $elsn = $this->c->p->findField('shortname');
                $elsn->setValue($shortname);
                $elf = $this->c->p->findField('format');
                if (isset($this->format)) {
                    $elf->selectOption($this->format);
                } else {
                    $this->format = $elf->getValue();
                }
                $btn = $this->c->p->findButton('Save changes');
                $btn->click();
                $this->c->reloadPage();
                $i++;
            } while ($error = $this->c->p->find('css', '.error'));
            $this->fullname  = $fullname;
            $this->shortname = $shortname;
            $this->url       = $this->c->s->getCurrentUrl();
            $this->clickNavLink();
            $this->clickNavLink();
            return $this;
        }
    }

    /**
     * Return a list of activity objects except those that are in eh skip array
     * @return array and array of activity objects
     */
    public function getAvailableActivities() {
        $activities = array();
        $this->clickAddActivity();

        $divs = $this->c->p->findAll('css', $this->addActivityCss);
        foreach ($divs as $div) {
            $id        = $div->getAttribute($this->addactivityAttr);
            $classes   = explode('_', $id);
            $classname = '\Auto\Activity\\' . ucfirst($classes[1] . 'Activity');

            if (class_exists($classname)) {
                $options      = array(
                    'c'     => $this->c,
                    'cssid' => $id,
                    'title' => ucfirst($classes[1]) . ' Activity Auto Created'
                );
                $activities[] = new $classname($options);
            } else {
                //$this->log->add('Could not find classname '.$classname);
            }
        }
        return $activities;
    }

    /**
     * Create one instance of all activities that are available and supported in a course.
     */
    public function createActivities() {
        $this->turnEditingOn();
        $activities = $this->getAvailableActivities();
        foreach ($activities as $activity) {
            if (method_exists($activity, 'create')) {
                if ($el = $this->getCreateAcitivty($activity)) {
                    $this->c->l->action($this->fullname . ': Creating ' . $activity->getTitle());
                    $el->doubleClick();
                    $this->c->reloadPage();
                    $activity->create();
                }
            }
        }
    }

    /**
     * Find the create activity element on the page based on the cssid for the activity
     *
     * @param \Behat\Mink\NodeElement activity The Mink activity object to click on
     *
     * @return bool|\Behat\Mink\NodeElement
     */
    public function getCreateAcitivity($activity) {
        if ($el = $this->c->p->findField($activity->getCssId())) {
            return $el;
        }
        return false;
    }

    /**
     * Click the add activity link in the course when editing is on
     */
    public function clickAddActivity() {
        $this->turnEditingOn();

        // Check if the activity chooser is even on.
        if ($chooser = $this->c->p->findLink('Activity chooser on')) {
            $this->c->l->action($this->fullname . ': Changing to activity chooser');
            $chooser->click();
            $this->c->reloadPage();
        }
        if ($addActivity = $this->c->p->find('css', '.section-modchooser-link a')) {
            $this->c->l->error($this->fullname . ': Clicking on add activity link');
            $addActivity->click();
        } else {
            $this->c->l->error($this->fullname . ': Could not find the add activity link');
            $this->clickNavLink();
        }
    }

    /**
     * Click the turn editing on button in a course and then reload the page.
     */
    public function turnEditingOn() {
        if ($btn = $this->c->p->findButton('Turn editing on')) {
            $this->c->l->error($this->fullname . ': Turning editing on');
            $btn->click();
        }
        $this->c->reloadPage();
    }

    /**
     * View the course page via the url
     */
    public function view() {
        $this->c->l->action('Viewing ' . $this->fullname);
        $this->c->s->visit($this->url);
    }

    /**
     * Set the course id
     *
     * @param string $id The course id in the Moodle database
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Get the course id
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set the course's url
     *
     * @param string $url and http url to the course for the site.
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * Get the url for the course
     * @return string The url to access the course
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set the fullname of the course, usually during creations no more than 255 characters.
     *
     * @param string $fullname Fullname for the course
     */
    public function setFullname($fullname) {
        $this->fullname = $fullname;
    }

    /**
     * Get the fullname of the course
     * @return string The full name of the course
     */
    public function getFullname() {
        return $this->fullname;
    }

    /**
     * Set the short name for the course no more then 100 characters.
     *
     * @param string $shortname
     */
    public function setShortname($shortname) {
        $this->shortname = $shortname;
    }

    /**
     * Get the short name of the course
     * @return string Shortname of the course
     */
    public function getShortname() {
        return $this->shortname;
    }

    /**
     * Set the course format that is being created. Should be one of the following:
     * <ul>
     * <li>flexpage</li>
     * <li>folderview</li>
     * <li>topics</li>
     * <li>weeks</li>
     * <ul>
     *
     * @param $format One of the course formats supported by Moodle.
     */
    public function setFormat($format) {
        $this->format = $format;
    }

    /**
     * Get the format the course was created in.
     * @return string The moodle course format
     */
    public function getFormat() {
        return $this->format;
    }
}