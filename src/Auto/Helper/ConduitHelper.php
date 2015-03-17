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
     * @param array  $fields fields to be passed to conduit.
     * @param string $action Create, update or delete action on the course
     *
     * @return bool|string Response from the server
     * @throws Exception Exception if there is an exception thrown by Guzzle
     */
    public function course($fields, $action = 'create') {
        $this->service = 'course';
        switch ($action) {
            case 'update':
            case 'create':
            case 'delete':
                $this->setXML($action, $fields);
                break;
            case 'get_course':
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
     * Course Conduit web service interactions happen in this method
     *
     * @param string $service    course, user, Enroll, groups, groups_members
     * @param array  $fieldGroup fields to be passed to conduit.
     * @param string $action     Create, update or delete action on the course
     *
     * @return bool|string Response from the server
     * @throws Exception Exception if there is an exception thrown by Guzzle
     */
    public function bulkService($service, $fieldGroup, $action = 'create') {
        $this->service = $service;
        $this->setBulkXML($action, $fieldGroup);

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
    public function enroll($fields, $action = 'create') {
        $this->service = 'enroll';

        $this->setXML($action, $fields);

        if ($response = $this->send()) {
            return $response;
        } else {
            throw new Exception($this->exception);
        }
    }

    /**
     * Create, Update or delete groups in a course
     *
     * @param        $fields
     * @param string $action
     *
     * @return bool|string
     * @throws Exception
     * @author Jason Hardin <jason@moodlerooms.com>
     */
    public function group($fields, $action = 'create') {
        $this->service = 'groups';

        $this->setXML($action, $fields);

        if ($response = $this->send()) {
            return $response;
        } else {
            throw new Exception($this->exception);
        }
    }

    /**
     * Create, Update or delete group membership in a course.
     *
     * @param        $fields
     * @param string $action
     *
     * @return bool|string
     * @throws Exception
     * @author Jason Hardin <jason@moodlerooms.com>
     */
    public function group_member($fields, $action = 'create') {
        $this->service = 'groups_members';

        $this->setXML($action, $fields);

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
     * Generate the XML for the webservice call
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
     * Generate the XML for the webservice call
     *
     * @param $action
     * @param $fieldGroup
     */
    private function setBulkXML($action, $fieldGroup) {
        $this->xml = '<?xml version="1.0" encoding="UTF-8"?><data>';
        foreach ($fieldGroup as $fields) {
            $this->xml .= '<datum action="' . $action . '">';
            foreach ($fields as $map => $value) {
                $this->xml .= '<mapping name="' . $map . '">' . $value . '</mapping>';
            }
            $this->xml .= '</datum>';
        }
        $this->xml .= '</data>';
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
        } catch (Guzzle\Http\Exception\CurlException $e) {
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
