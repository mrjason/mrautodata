<?php
/**
 * CondiuitHelper class
 * @package    Helper
 * @subpackage ConduitHelper
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Helper;

use Guzzle\Service\Client;
use Symfony\Component\Console\Helper\Helper;

/**
 * ConduitHelper displays the help for a given command.
 */
class ConduitHelper extends Helper {
    /**
     * @var string The site that the restful commands will be sent to
     */
    protected $url;
    /**
     * @var string The method to be executed by Conduit
     */
    protected $method = 'handle';
    /**
     * @var string The Conduit service to send the request to
     */
    protected $service;
    /**
     * @var string The password to execute Conduit commands on the site
     */
    protected $token = '';
    /**
     * @var string The XML to send to Conduit for processing
     */
    protected $xml;
    /**
     * @var The exception object returned by Guzzle
     */
    protected $exception;

    /**
     * Set the token to be passed to Conduit's web services for authorization
     *
     * @param $token
     *
     * @author Jason Hardin <jason@moodlerooms.com>
     */
    public function setToken($token) {
        $this->token = $token;
    }

    /**
     * Set the url to send the Conduit request to
     *
     * @param string $url The url for the server to send the Conduit information to
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * Set the Conduit method to process the request with
     *
     * @param string $method Conduit method
     */
    public function setMethod($method) {
        $this->method = $method;
    }

    /**
     * Course Conduit web service interactions happen in this method
     *
     * @param \stdClass $course A course object with the variables that are needed to perform the action on the course
     * @param string    $action Create, update or delete action on the couse
     *
     * @return bool|string Response from the server
     * @throws Exception Exception if there is an exception thrown by Guzzle
     */
    public function course($course, $action = 'create') {
        $this->service = 'course';
        switch ($action) {
            case 'update':
            case 'create':
                $fields = array(
                    'shortname'        => $course->shortname,
                    'fullname'         => $course->fullname,
                    'idnumber'         => $course->idnumber,
                    'format'           => $course->format,
                    'category'         => $course->category,
                    'groupmode'        => $course->groupmode,
                    'visible'          => $course->visible,
                    'enablecompletion' => $course->enablecompletion,
                    'coursetemplate'   => $course->coursetemplate
                );
                $this->setXML($action, $fields);
                break;
            case 'delete':
                $fields = array('shortname' => $course->shortname);
                $this->setXML($action, $fields);
                break;
            case 'get_course':
                $fields = array(
                    'field' => 'shortname',
                    'value' => $course->shortname
                );
                $this->addPostFields($fields);
                break;
        }

        if ($response = $this->send()) {
            return $response;
        } else {
            throw new Exception($this->exception);
        }
    }

    /**
     * Enroll a user in a course with a role
     *
     * @param     $user
     * @param     $course
     * @param     $role
     * @param int $start
     * @param int $end
     *
     * @return bool|string
     * @throws Exception
     */
    public function enroll($user, $course, $role, $start = 0, $end = 0) {
        $this->service = 'enroll';
        $fields        = array(
            'course' => $course,
            'user'   => $user,
            'role'   => $role,
            'status' => '0'
        );
        if ($start) {
            $fields['start'] = $start;
        }

        if ($end) {
            $fields['end'] = $end;
        }

        $this->setXML('create', $fields);

        if ($response = $this->send()) {
            return $response;
        } else {
            throw new Exception($this->exception);
        }
    }

    /**
     * Create, update or delete a group in a course
     *
     * @param $course
     * @param $group
     *
     * @return bool|string
     * @throws Exception
     */
    public function groups($course, $group) {
        $this->service = 'groups';
        $fields        = array(
            'course'      => $course,
            'group'       => $group,
            'description' => 'This is a student group called ' . $group
        );

        $this->setXML('create', $fields);

        if ($response = $this->send()) {
            return $response;
        } else {
            throw new Exception($this->exception);
        }
    }

    /**
     * Create, Update or delete group membership in a course.
     *
     * @param $course
     * @param $group
     * @param $user
     *
     * @return bool|string
     * @throws Exception
     */
    public function groups_members($course, $group, $user) {
        $this->service = 'groups_members';
        $fields        = array(
            'course' => $course,
            'group'  => $group,
            'user'   => $user
        );

        $this->setXML('create', $fields);

        if ($response = $this->send()) {
            return $response;
        } else {
            throw new Exception($this->exception);
        }
    }

    /**
     * Create, update or delete a user from the Moodle site
     *
     * @param        $fields
     * @param string $action
     *
     * @return bool|string
     * @throws Exception
     */
    public function user($fields, $action = 'update') {
        $this->service = 'user';
        $this->setXML($action, $fields);

        if ($response = $this->send()) {
            return $response;
        } else {
            throw new Exception($this->exception);
        }
    }

    /**
     * Gnerate the XML for the webservice call
     *
     * @param $action
     * @param $fields
     */
    private function setXML($action, $fields) {
        $this->xml = '<?xml version="1.0" encoding="UTF-8"?><data><datum action="' . $action . '">';
        foreach ($fields as $map => $value) {
            $this->xml .= '<mapping name="' . $map . '">' . $value . '</mapping>';
        }
        $this->xml .= '</datum></data>';
    }

    /**
     * Set the base fields for the post to the webservice
     */
    private function setBasePostFields() {
        $this->postFields['token']  = $this->token;
        $this->postFields['method'] = $this->method;

        if (!empty($this->xml)) {
            $this->postFields['xml'] = $this->xml;
        }

    }

    /**
     * Add fields to the post
     *
     * @param $fields
     */
    private function addPostFields($fields) {
        foreach ($fields as $name => $value) {
            $this->postFields[$name] = $value;
        }
    }

    /**
     * Send the webservice request
     *
     * @return bool|string
     */
    private function send() {
        $this->setBasePostFields();
        $client = new Client('http://' . $this->url . '/blocks/conduit/webservices/rest/', array(
                                                                                                'curl.CURLOPT_CONNECTTIMEOUT' => 100,
                                                                                                'curl.CURLOPT_TIMEOUT'        => 60,
                                                                                           ));
        try {
            $request = $client->post('http://' . $this->url . '/blocks/conduit/webservices/rest/' . $this->service . '.php')
                ->addPostFields($this->postFields);
        } catch (Guzzle\Http\Exception\BadResponseException $e) {
            $this->exception = $e;
            return false;
        }
        $response = $request->send();
        if ($body = @simplexml_load_string($response->getBody())) {
            if (isset($body->handle->response->message)) {
                $msg = $body->handle->response->message;
            } else {
                $msg = $body->handle->response;
            }
            return ('Site: ' . $this->url . '  Service:' . $this->service . ' Status: ' . $body->handle->status . " Response: $msg \n");
        } else {
            return ($response);
        }
    }

    /**
     * Returns the canonical name of this helper.
     * @return string The canonical name
     * @api
     */
    public function getName() {
        return 'conduit';
    }
}

?>
