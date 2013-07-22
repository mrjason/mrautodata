<?php
/**
 * UserHelper class
 *
 * @package Helper
 * @subpackage UserHelper
 * @author Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Helper;

use Symfony\Component\Console\Helper\Helper;

/**
 * UserHelper Class to assist with setting up the moodle user data
 */
class UserHelper extends Helper {
    /**
     * @var array
     */
    private $ids = array();
    /**
     * @var string
     */
    private $username = 'user';
    /**
     * @var string
     */
    private $role = 'student';
    /**
     * @var string
     */
    private $password = '';
    /**
     * @var array
     */
    /**
     * @var array
     */
    private $groups = array('Group A', 'Group B', 'Group C');
    /**
     * @var int
     */
    private $index = 0;
    /**
     * @var array
     */
    private $studentroles = array('student');

    /**
     * @return array
     */
    public function getUsers(){
        $users = array();

        if(!empty($this->ids)){
            foreach($this->ids as $id) {
                $user = new \stdClass();
                $user->username = $this->username.$id;
                $user->password = $this->password;
                $user->role = $this->role;
                $user->email = $user->username.'@dev.mroomstest.net';
                $user->id = $this->role.$this->username.rand();
                $user->index = $id;
                if(in_array($user->role,$this->studentroles)){
                    if($id < count($this->groups)){
                        $user->group = $this->groups[$id];
                    } else {
                        $user->group = $this->groups[($id % count($this->groups))];
                    }
                }
                $users[] = $user;
            }
        } else {
            $user = new \stdClass();
            $user->username = $this->username;
            $user->password = $this->password;
            $user->role = $this->role;
            $user->email = $user->username.'@dev.mroomstest.net';
            $user->id = $this->role.$this->username.rand();
            $user->index = $this->index;
            if(in_array($user->role,$this->studentroles)){
                $user->group = $this->groups[0];
            }

            $users[] = $user;
        }
        return $users;
    }

    /**
     * @param $username
     */
    public function setUsername($username){
        $this->username = $username;
    }

    /**
     * @param array $ids
     * @param bool $continuous
     */
    public function setUserIds(array $ids, $continuous = true){
        if($continuous){
            $this->ids = array();
            for($i = $ids[0]; $i < $ids[1]; $i++){
                $this->ids[] = $i;
            }
        } else{
            $this->ids = $ids;
        }
    }

    /**
     * @param $index
     */
    public function setIndex($index){
        $this->index = $index;
    }

    /**
     * @param $role
     */
    public function setRole($role){
        $this->role = $role;
    }

    /**
     * @param $password
     */
    public function setPassword($password){
        $this->password = $password;
    }
    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     *
     * @api
     */
    public function getName(){
        return 'user';
    }
}
?>