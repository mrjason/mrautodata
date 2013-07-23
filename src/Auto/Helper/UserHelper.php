<?php
/**
 * UserHelper class
 * @package    Helper
 * @subpackage UserHelper
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Helper;

use Symfony\Component\Console\Helper\Helper;

/**
 * UserHelper Class to assist with setting up the moodle user data
 */
class UserHelper extends Helper {
    /**
     * ids to append to the username
     *
     * @var array
     */
    private $ids = array();
    /**
     * The username for the user also login
     *
     * @var string
     */
    private $username = 'user';
    /**
     * The user's role in the Moodle courses
     *
     * @var string
     */
    private $role = 'student';
    /**
     * The user's password to login with
     *
     * @var string
     */
    private $password = '';

    /**
     * Groups in a course
     *
     * @var array
     */
    private $groups = array('Group A', 'Group B', 'Group C');
    /**
     * Number of user's to create
     *
     * @var int
     */
    private $index = 0;
    /**
     * The Moodle role shortname for a student in a course.
     * 
     * @var array
     */
    private $studentroles = array('student');

    /**
     * Return An array of users based on the ids assigned or the current username.
     *
     * @return array Array of User objects.
     */
    public function getUsers() {
        $users = array();

        if (!empty($this->ids)) {
            foreach ($this->ids as $id) {
                $user           = new \stdClass();
                $user->username = $this->username . $id;
                $user->password = $this->password;
                $user->role     = $this->role;
                $user->email    = $user->username . '@dev.mroomstest.net';
                $user->id       = $this->role . $this->username . rand();
                $user->index    = $id;
                if (in_array($user->role, $this->studentroles)) {
                    if ($id < count($this->groups)) {
                        $user->group = $this->groups[$id];
                    } else {
                        $user->group = $this->groups[($id % count($this->groups))];
                    }
                }
                $users[] = $user;
            }
        } else {
            $user           = new \stdClass();
            $user->username = $this->username;
            $user->password = $this->password;
            $user->role     = $this->role;
            $user->email    = $user->username . '@dev.mroomstest.net';
            $user->id       = $this->role . $this->username . rand();
            $user->index    = $this->index;
            if (in_array($user->role, $this->studentroles)) {
                $user->group = $this->groups[0];
            }

            $users[] = $user;
        }
        return $users;
    }

    /**
     * Set the current username to create users from.
     *
     * @param $username The username or prefix to create users from.
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * Set the array of ids to use for creating users.
     *
     * @param array $ids Ids to be added to the end of the username
     * @param bool  $continuous Are the id's continuous and can be looped through?
     */
    public function setUserIds(array $ids, $continuous = true) {
        if ($continuous) {
            $this->ids = array();
            for ($i = $ids[0]; $i < $ids[1]; $i++) {
                $this->ids[] = $i;
            }
        } else {
            $this->ids = $ids;
        }
    }

    /**
     * Set the current index for creating ids
     *
     * @param $index
     */
    public function setIndex($index) {
        $this->index = $index;
    }

    /**
     * Set the user's Moodle shortname role
     *
     * @param $role Moodle shortname role
     */
    public function setRole($role) {
        $this->role = $role;
    }

    /**
     * Set the user's password
     *
     * @param $password User's password plaintext.
     */
    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * Returns the canonical name of this helper.
     * @return string The canonical name
     * @api
     */
    public function getName() {
        return 'user';
    }
}

?>