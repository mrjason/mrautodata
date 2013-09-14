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
    protected $container;

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
        $this->container->reloadPage($this->fullname);
    }

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
                        'container'     => $this->container,
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
        $this->container->logHelper->action($this->fullname . ': Getting all course sections');
        $elementSections = $this->container->page->findAll('css', 'div.course-content ul li');

        $sections = array();
        foreach ($elementSections as $element) {
            $section     = new \stdClass;
            $section->id = $element->getAttribute('id');
            if ($elementName = $this->container->page->find('css', 'h3.sectionname')) {
                $section->name = $elementName->getText();
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
        if ($element = $this->container->page->findLink($this->fullname)) {
            $this->container->logHelper->action($this->fullname . ': Clicked full name link');
            $element->click();
            $this->container->reloadPage($this->fullname);
        } else {
            if ($this->container->cfg->debug) {
                $this->container->logHelper->action($this->fullname . ': Can\'t find current course link');
            }
            if (isset($this->url)) {
                $this->container->logHelper->action($this->fullname . ': Returning to course the course via the url ' . $this->url);
                $this->container->visit($this->url);
            }
        }
    }

    /**
     * This method clicks on the course link in the navigation bar
     * @access public
     */
    public function clickNavLink() {
        if ($navbar = $this->container->page->find('css', '.navbar')) {
            if ($element = $navbar->findLink($this->fullname)) {
                $this->container->logHelper->action($this->fullname . ': Clicked on Course Nav link');
                $element->click();
                $this->container->reloadPage($this->fullname);
            }
        } else if (isset($this->url)) {
            $this->container->logHelper->action($this->fullname . ': Returning to course with url ' . $this->url);
            $this->container->visit($this->url);
        }
    }

    /**
     * Create a course based on the currently set course. If the course shortname exists increment until the course can be created.
     */
    public function create() {
        $i = 0;

        $this->container->visit('/course/index.php?categoryedit=on');
        $this->container->logHelper->action('Adding course');
        if ($button = $this->container->page->findButton('Add a new course')) {
            $button->click();
            $this->container->reloadPage($this->fullname);
            do {
                $fullname  = $this->fullname . $i;
                $shortname = $this->shortname . $i;
                $elementfn      = $this->container->page->findField('fullname');
                $elementfn->setValue($fullname);
                $elementsn = $this->container->page->findField('shortname');
                $elementsn->setValue($shortname);
                $elementf = $this->container->page->findField('format');
                if (isset($this->format)) {
                    $elementf->selectOption($this->format);
                } else {
                    $this->format = $elementf->getValue();
                }
                $button = $this->container->page->findButton('Save changes');
                $button->click();
                $this->container->reloadPage($this->fullname);
                $i++;
            } while ($error = $this->container->page->find('css', '.error'));
            $this->fullname  = $fullname;
            $this->shortname = $shortname;
            $this->url       = $this->container->session->getCurrentUrl();
            $this->containerlickNavLink();
            $this->containerlickNavLink();
            return $this;
        }
    }

    /**
     * Return a list of activity objects except those that are in eh skip array
     * @return array and array of activity objects
     */
    public function getAvailableActivities() {
        $activities = array();
        $this->containerlickAddActivity();

        $divs = $this->container->page->findAll('css', $this->addActivityCss);
        foreach ($divs as $div) {
            $id        = $div->getAttribute($this->addactivityAttr);
            $classes   = explode('_', $id);
            $classname = '\Auto\Activity\\' . ucfirst($classes[1] . 'Activity');

            if (class_exists($classname)) {
                $options      = array(
                    'container'     => $this->container,
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
                if ($element = $this->getCreateAcitivty($activity)) {
                    $this->container->logHelper->action($this->fullname . ': Creating ' . $activity->getTitle());
                    $element->doubleClick();
                    $this->container->reloadPage($this->fullname);
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
        if ($element = $this->container->page->findField($activity->getCssId())) {
            return $element;
        }
        return false;
    }

    /**
     * Click the add activity link in the course when editing is on
     */
    public function clickAddActivity() {
        $this->turnEditingOn();

        // Check if the activity chooser is even on.
        if ($chooser = $this->container->page->findLink('Activity chooser on')) {
            $this->container->logHelper->action($this->fullname . ': Changing to activity chooser');
            $chooser->click();
            $this->container->reloadPage($this->fullname);
        }
        if ($addActivity = $this->container->page->find('css', '.section-modchooser-link a')) {
            $this->container->logHelper->error($this->fullname . ': Clicking on add activity link');
            $addActivity->click();
        } else {
            $this->container->logHelper->error($this->fullname . ': Could not find the add activity link');
            $this->containerlickNavLink();
        }
    }

    /**
     * Click the turn editing on button in a course and then reload the page.
     */
    public function turnEditingOn() {
        if ($button = $this->container->page->findButton('Turn editing on')) {
            $this->container->logHelper->error($this->fullname . ': Turning editing on');
            $button->click();
        }
        $this->container->reloadPage($this->fullname);
    }

    /**
     * View the course page via the url
     */
    public function view() {
        $this->container->logHelper->action($this->fullname . ': Viewing');
        $this->container->session->visit($this->url);
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