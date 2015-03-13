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
use Symfony\Component\Yaml\Parser;

/**
 * UserHelper Class to assist with setting up the moodle user data
 */
class UserHelper extends Helper {
    /**
     * ids to append to the username
     * @var array
     */
    private $ids = array();
    /**
     * The username for the user also login
     * @var string
     */
    private $basename = 'user';
    /**
     * The user's password to login with
     * @var string
     */
    private $password = '';

    /**
     * Groups in a course
     * @var array
     */
    private $groups = array('Group A', 'Group B', 'Group C');
    /**
     * Number of user's to create
     * @var int
     */
    private $index = 0;

    /**
     * @var mixed Load of the users file in the YAML resources
     */
    private $names;

    public function loadUsers($lang = "EN"){
        $yaml = new Parser();
        $filename =  __DIR__ . '/../Resources/Yaml/Lang/EN/Users.yml';

        if(file_exists(__DIR__ . '/../Resources/Yaml/Lang/' . $lang . '/Users.yml')){
            $filename = __DIR__ . '/../Resources/Yaml/Lang/' . $lang . '/Users.yml';
        }
        try {
            $this->names = $yaml->parse(file_get_contents($filename));
        } catch (ParseException $e) {
            printf("Unable to parse the YAML string: %s", $e->getMessage());
        }
    }

    /**
     * Return An array of users based on the ids assigned or the current username.
     * @return array Array of User objects.
     */
    public function getUsers() {
        $users = array();

        if (!empty($this->ids)) {
            foreach ($this->ids as $id) {
                if($id > 0) {
                    $username = $this->basename . ($id+1);
                } else {
                    $username = $this->basename;
                }
                $name = $this->names[$this->basename][$id];

                $user            = new \stdClass();
                $user->username  = $username;
                $user->firstname = $name['firstname'];
                $user->lastname  = $name['lastname'];
                $user->password  = $this->password;
                $user->email     = $username . '@dev.mroomstest.net';
                $user->id        = $this->basename . rand();
                $user->index     = $id;
                $users[] = $user;
            }
        } else {
            $users = $this->getRandomNamedUsers();
        }
        return $users;
    }

    public function getRandomNamedUsers(){
        $users = array();

        if (!empty($this->ids)) {
            foreach ($this->ids as $id) {
                $randFirst = rand(0, count($this->names['firstname']) - 1);
                $randLast  = rand(0, count($this->names['lastname']) - 1);
                if($id > 0) {
                    $username = $this->basename . $id;
                } else {
                    $username = $this->basename;
                }

                $user            = new \stdClass();
                $user->username  = $username;
                $user->firstname = $this->names['firstname'][$randFirst];
                $user->lastname  = $this->names['lastname'][$randLast];
                $user->password  = $this->password;
                $user->email     = $username . '@dev.mroomstest.net';
                $user->id        = $this->basename . rand();
                $user->index     = $id;
                $users[] = $user;
            }
        } else {
            $randFirst = rand(0, count($this->names->firstname) - 1);
            $randLast  = rand(0, count($this->names->lastname) - 1);
            $user            = new \stdClass();

            $user->username  = $this->basename;
            $user->firstname = $this->names['firstname'][$randFirst];
            $user->lastname  = $this->names['lastname'][$randLast];
            $user->password  = $this->password;
            $user->email     = $user->username . '@dev.mroomstest.net';
            $user->id        = $this->basename . rand();
            $user->index     = $this->index;

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
        $this->basename = $username;
    }

    /**
     * Set the array of ids to use for creating users.
     *
     * @param array $ids        Ids to be added to the end of the username
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