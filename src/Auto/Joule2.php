<?php
/**
 * Joule 2  class file.
 *
 * @package
 * @author Jason Hardin <jason@moodlerooms.com>
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
     *
     * @var object
     */
    protected $user;

    /**
     * @var Container Class containing all variables for the session, page, log helper and content helper.
     */
    protected $c;

    /**
     * Construct the Joule2 classes for the run
     *
     * @param \Auto\Container $container new container variable to be used in the process
     */
    public function __construct($container){
        $this->c = $container;
    }

    /**
     * Setup the browser, site url and host from the passed config file.  Starts the selenium server instance and opens the browser url.
     *
     * @access public
     * @param object $site The Site object to be used and accessed in the process.
     */
    public function setSite($site) {
        $this->site = $site;
        $this->c->setBaseUrl($site->url);
        $this->c->l->setSite($site);
    }

    /**
     * This method wraps around the Container's teardown method
     */
    public function teardown(){
        $this->c->teardown();
    }

    /**
     * This method wraps around the Container cleanup function
     */
    public function cleanup(){
        $this->c->cleanup();
    }

    /**
     * Set the current user object.
     *
     * @access public
     * @param object $user The current user to be used. see the User variable.
     */
    public function setUser($user){
        $this->user = $user;
    }

    /**
     * This method logs the user into the site and requires that the current user has been set.
     *
     * @access public
     * @param stdClass $user The current user to be used. see the User variable.
     */
    public function login($user = ''){
        if(!empty($user)){
            $this->user = $user;
        }
        $this->c->visit('/login/index.php');
        $this->c->reloadPage();
        if ($username = $this->c->p->findField('username')){
            $username->setValue($this->user->username);
            if($password = $this->c->p->findField('password')){
                $password->setValue($this->user->password);
            }
            if($btn = $this->c->p->findButton('Login')){
                $btn->press();
                $this->c->l->action($this->site->url. ': Logged in with username '.$this->user->username);
                return true;
            } else {
                $this->c->l->failure($this->site->url. ': Could not find login button');
                return false;
            }
        } else {
            $this->c->l->failure($this->site->url. ': Could not find username field in the login area');
            return false;
        }
        $this->c->reloadPage();
        if($this->c->p->find('css','div.loginerrors')){
            $this->c->l->failure($this->site->url. ': Error in Login for '.$this->user->username.' '.$this->c->p->find('css','div.loginerrors > span')->getText());
            return false;
        }
    }

    /**
     * This method logs the user out from the site.
     *
     * @access public
     */
    public function logout(){
        try{
            $this->c->visit('/login/logout.php');
            $this->c->reloadPage();
            if($continue = $this->c->p->findButton('Continue')){
                $continue->click();
            }
        } catch(\Exception $e){
            $this->c->l->error($e);
        }
    }

    /**
     * This method returns the courses the user is enrolled in that show either on the My Moodle or site course pages.
     *
     * @return array and array of course objects.
     */
    public function getCourses(){
        $this->c->l->action($this->site->url. ': Getting all courses');
        /// Lets try my Moodle first as this seems the most common list of courses that Sale directors don't change.
        $this->c->visit('/my');
        $courses =  array();
        $elCourses = $this->c->p->findAll('css','div.coursebox > h3.main > a');

        $coursecount = count($elCourses) + 1;

    /// If My moodle had nothing lets try the front page for the list of courses.
        if($coursecount == 1) {
            $this->c->visit();
            $elCourses = $this->c->p->findAll('css','div.coursebox > div.info > h3.main > a');
            $coursecount = count($elCourses) + 1;
        }

        if($coursecount > 1){
            foreach($elCourses as $el){
                $url = $el->getAttribute('href');
                $ids = preg_split('/id=/',$url);
                $options = array('c' => $this->c,
                    'url' => $url,
                    'fullname' => $el->getText(),
                    'id' => $ids[1]
                );
                $course = new \Auto\Course\Course($options);
                $courses[] = $course;
            }
        }

        return $courses;
    }

    /**
     * This method clicks on the Home link in the navigation breadcrumb.
     */
    public function goToSitePage(){
        if($el = $this->c->p->findLink('Home')){
            $this->c->l->action($this->site->url. ': Clicked Home link');
            $el->click();
            $this->c->reloadPage();
        } else {
            $this->c->l->action($this->site->url. ': Going to the base page');
            $this->c->visit();
        }
    }

    /**
     * This method determines which action to take based on the class of the LI tag for the activity based on the CMID passed.
     * Behaviors are:
     * <ul>
     * <li>forum: create discussion</li>
     * <li>assignment: post to assignment</li>
     * <li>quiz: attempt quiz</li>
     * <li>resource: view resource</li>
     * </ul>
     *
     * @access public
     * @param stdClass $activity the cmid of the module that is being interacted with
     * @param integer $activityLevel the percentage of time an activity shoudl be intereacted with, 10% means 1 out of every 10 activities is veiwed.
     * @param array $gradeRange an array with the minimum and maximum grade a user should get.  User to cacluate how often the user is correct or a teacher grades them at.
     * @return  boolean Return if the activity was interacted iwht or not.
     */
    public function interactWithActivity($activity, $grade){
        if($this->user->role == 'student'){
            $activity->interact();
        } else if ($this->user->role == 'teacher'){
            if(method_exists($activity, 'grade')){
                $activity->grade($grade);
            }
        }
        $this->c->reloadPage();
        return true;
    }

    /**
     * This method marks all activities in a course complete by the user.
     *
     * @param array $activities an array of activity objects to be marked
     */
    public function manualMarkAllActivitiesComplete($activities){
        foreach($activities as $activity){
            $activity->complete();
        }
    }

    /**
     * Marks a random activity in the course as completed by a student
     *
     * @param array $activities array of activity objects
     * @param int $tocomplete a number of activities tobe complete, should be less than the number of activities.
     */
    public function manualMarkRandomActivityComplete($activities, $tocomplete){
        if($tocomplete > count($activities)){
            $this->manualMarkAllActivitiesComplete($activities);
        } else {
            for($i = 0; $i < $tocomplete; $i){
                $j = rand(0, count($activities)-1);
                $activities[$j]->complete();
            }
        }
    }

    /**
     * This method uploads a profile picture to Moodle for the user based on a file name and location provided
     *
     * @param $filename The file to be uploaded in the demo files directory
     */
    public function addProfilePicture($filename){
        $this->c->l->action($this->site->url. ': Editing Profile');
        $this->c->visit('/user/edit.php');
        $this->c->ch->addFile($this->c->cf->filedir.'/'.$filename);
        $button = $this->c->p->findButton('Update profile');
        try{
            $button->press();
        } catch(Exception $e){
            //do nothing because the likely issue is an alert that we can't handle.
        }
        $this->c->reloadPage();
    }

    /**
     * Create a course based on the currently set course. If the course shortname exists increment until the course can be created.
     *
     * @param $settings Course settings to be created
     * @return \Auto\Course Course object that was created
     */
    public function createCourse($settings){
        $classname = '\Auto\Course\\'.ucfirst($settings['format'].'Course');
        $course = new $classname(array_merge(array('c'=>$this->c),$settings));
        $course->create();
        return $course;
    }
}
?>
