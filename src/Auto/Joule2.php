<?php
/**
 * Joule 2  class file.
 * @package
 * @author    Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto;

/**
 * This class is designed to script common actions within a Moodle site to generate data similar to a student or teacher's actions.
 */
class Joule2 {

    /**
     * @var \stdClass Site object created by the SiteHelper
     */
    protected $site;

    /**
     * The current user that will be logged in and actions run for.  The object contains the following variables:
     * <ul>
     * <li>id (optional): used by grading functions</li>
     * <li>username: used to log the user in</li>
     * <li>password: used to log the user in</li>
     * </ul>
     * @var object
     */
    protected $user;

    /**
     * @var Container Class containing all variables for the session, page, log helper and content helper.
     */
    protected $container;

    /**
     * Construct the Joule2 classes for the run
     *
     * @param \Auto\Container $container new container variable to be used in the process
     */
    public function __construct($container) {
        $this->container = $container;
    }

    /**
     * Setup the browser, site url and host from the passed config file.  Starts the selenium server instance and opens the browser url.
     * @access public
     *
     * @param object $site The Site object to be used and accessed in the process.
     */
    public function setSite($site) {
        $this->site = $site;
        $this->container->setBaseUrl($site->url);
        $this->container->logHelper->setSite($site);
    }

    /**
     * This method wraps around the Container's teardown method
     */
    public function teardown() {
        $this->container->teardown();
    }

    /**
     * This method wraps around the Container cleanup function
     */
    public function cleanup() {
        $this->container->cleanup();
    }

    /**
     * Set the current user object.
     * @access public
     *
     * @param object $user The current user to be used. see the User variable.
     */
    public function setUser($user) {
        $this->user = $user;
    }

    /**
     * The function logs the user into the site
     *
     * @param string $user The user to login as
     *
     * @return bool
     * @author Jason Hardin <jason@moodlerooms.com>
     */
    public function login($user = '') {
        if (!empty($user)) {
            $this->user = $user;
        }
        $this->container->visit('/login/index.php');
        $this->container->reloadPage($this->site->url);
        if ($username = $this->container->page->findField('username')) {
            $username->setValue($this->user->username);
            if ($password = $this->container->page->findField('password')) {
                $password->setValue($this->user->password);
            }
            if ($button = $this->container->page->findButton('Log in')) {
                $button->press();
            } else {
                $this->container->logHelper->failure($this->site->url . ': Could not find login button');
                return false;
            }
        } else {
            $this->container->logHelper->failure($this->site->url . ': Could not find username field in the login area');
            return false;
        }
        $this->container->reloadPage($this->site->url);
        if ($this->container->page->find('css', 'div.loginerrors')) {
            $this->container->logHelper->failure($this->site->url . ': Error in Login for ' . $this->user->username . ' ' . $this->container->page->find('css', 'div.loginerrors > span')->getText());
            return false;
        }
        $this->container->logHelper->action($this->site->url . ': Logged in with username ' . $this->user->username);
        return true;
    }

    /**
     * This method logs the user out from the site.
     * @access public
     */
    public function logout() {
        try {
            $this->container->visit('/login/logout.php');
            $this->container->reloadPage($this->site->url);
            if ($continue = $this->container->page->findButton('Continue')) {
                $continue->click();
            }
        } catch (\Exception $e) {
            $this->container->logHelper->error($e);
        }
    }

    /**
     * This method returns the courses the user is enrolled in that show either on the My Moodle or site course pages.
     * @return array and array of course objects.
     */
    public function getCourses() {
        $this->container->logHelper->action($this->site->url . ': Getting all courses');
        /// Lets try my Moodle first as this seems the most common list of courses that Sale directors don't change.
        $this->container->visit('/my');
        $courses        = array();
        $elementCourses = $this->container->page->findAll('css', 'div.course_title > h2.title > a');

        $coursecount = count($elementCourses) + 1;

        /// If My moodle had nothing lets try the front page for the list of courses.
        if ($coursecount == 1) {
            $this->container->visit();
            $elementCourses = $this->container->page->findAll('css', 'div.coursebox > div.info > h3 > a');
            $coursecount    = count($elementCourses) + 1;
        }

        if ($coursecount > 1) {
            foreach ($elementCourses as $element) {
                $url       = $element->getAttribute('href');
                $ids       = preg_split('/id=/', $url);
                $options   = array(
                    'container' => $this->container,
                    'url'       => $url,
                    'fullname'  => $element->getText(),
                    'id'        => $ids[1]
                );
                $course    = new \Auto\Course\Course($options);
                $courses[] = $course;
            }
        }

        return $courses;
    }

    /**
     * This method clicks on the Home link in the navigation breadcrumb.
     */
    public function goToSitePage() {
        if ($element = $this->container->page->findLink('Home')) {
            $this->container->logHelper->action($this->site->url . ': Clicked Home link');
            $element->click();
            $this->container->reloadPage($this->site->url);
        } else {
            $this->container->logHelper->action($this->site->url . ': Going to the base page');
            $this->container->visit();
        }
    }

    /**
     * Executes the interact method of the activity object passed.
     *
     * @param object $activity The activity object to interact with.
     * @param int    $grade    The grade to give the activity.
     *
     * @return bool
     * @author Jason Hardin <jason@moodlerooms.com>
     */
    public function interactWithActivity($activity, $grade) {
        switch ($this->user->role) {
            case 'teacher':
                $activity->teacherInteract($grade);
                break;
            default:
                $activity->interact();
        }

        return true;
    }

    /**
     * This method marks all activities in a course complete by the user.
     *
     * @param array $activities an array of activity objects to be marked
     */
    public function manualMarkAllActivitiesComplete($activities) {
        foreach ($activities as $activity) {
            $activity->complete();
        }
    }

    /**
     * Marks a random activity in the course as completed by a student
     *
     * @param array $activities array of activity objects
     * @param int   $tocomplete a number of activities tobe complete, should be less than the number of activities.
     */
    public function manualMarkRandomActivityComplete($activities, $tocomplete) {
        if ($tocomplete > count($activities)) {
            $this->manualMarkAllActivitiesComplete($activities);
        } else {
            for ($i = 0; $i < $tocomplete; $i) {
                $j = rand(0, count($activities) - 1);
                $activities[$j]->complete();
            }
        }
    }

    /**
     * This method uploads a profile picture to Moodle for the user based on a file name and location provided
     *
     * @param $filename The file to be uploaded in the demo files directory
     */
    public function addProfilePicture($filename) {
        $fullpathfile = $this->container->cfg->filedir . '/' . $filename;
        if(file_exists($fullpathfile)) {
            try {
                $this->container->logHelper->action($this->site->url . ': Editing Profile');
                $this->container->visit('/user/edit.php');
                $pictureLink = $this->container->page->find('css', '#id_moodle_picture a');
                $pictureLink->click();
                $this->container->reloadPage($this->site->url);
                $this->container->contentHelper->addFile($fullpathfile);
                $this->container->session->wait(5000);
                $button = $this->container->page->findButton('Update profile');
                $button->press();
            } catch (Exception $e) {
                //do nothing because the likely issue is an alert that we can't handle.
            }
            $this->container->reloadPage($this->site->url);
        }
    }

    /**
     * Create a course based on the currently set course. If the course shortname exists increment until the course can be created.
     *
     * @param $settings Course settings to be created
     *
     * @return \Auto\Course Course object that was created
     */
    public function createCourse($settings) {
        $classname = '\Auto\Course\\' . ucfirst($settings['format'] . 'Course');
        $course    = new $classname(array_merge(array('container' => $this->container), $settings));
        $course->create();
        return $course;
    }
}

?>
