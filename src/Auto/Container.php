<?php
/**
 * Cpntainer class
 * @package   Container
 * @author    Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto;

use Behat\Mink\Driver\SahiDriver;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Driver\ZombieDriver;
use Behat\Mink\Session;

/**
 * A Container class to contain all of the variables that are used by the majority of the other classes in Auto.
 */
class Container {
    /**
     * @var cfg The server configuration object
     */
    public $cfg;
    /**
     * @var \Behat\Mink\Session The Mink Session object.
     */
    public $session;
    /**
     * @var object The Mink object returned from session->getPage()
     */
    public $page;
    /**
     * @var LogHelper LogHelper object to access that class's functions.
     */
    public $logHelper;
    /**
     * @var string The base url for the site and session.
     */
    public $baseUrl;

    /**
     * Constructor for the Container object
     *
     * @param ConfigHelper  $cfg           ConfigHelper object to grab all of the server settings.
     * @param LogHelper     $logHelper     Create a new LogHelper object and pass it here
     * @param ContentHelper $contentHelper ContentHelper object
     */
    public function __construct($cfg, $contentHelper, $logHelper) {
        $this->cfg            = new \stdClass();
        $this->cfg->delay     = $cfg->get('server', 'delay');
        $this->cfg->driver    = $cfg->get('server', 'driver');
        $this->cfg->verbose   = $cfg->get('notifications', 'verbose');
        $this->cfg->emaillogs = $cfg->get('notifications', 'emaillogs');
        $this->cfg->filedir   = $cfg->get('dirs', 'file');
        $this->cfg->ddir      = $cfg->get('dirs', 'download');
        $this->cfg->lang      = $cfg->getLanguage();
        $this->contentHelper  = $contentHelper;
        $this->logHelper      = $logHelper;
        $this->logHelper->init();

        try {
            switch ($this->cfg->driver) {
                case 'zombie':
                    $driver = new ZombieDriver('127.0.0.1', 8124);
                    break;
                case 'sahi':
                    $driver = new SahiDriver('firefox'); //This doesn't seem to work and hasn't been updated since 2011
                    break;
                default:
                    $driver = new Selenium2Driver();
                    break;

            }
            $this->session = new Session($driver);
            $this->session->start();
            $this->contentHelper->setUp($this);
        } catch (Exception $e) {
            $this->logHelper->action($e->msg);
            $this->cleanup();
        }
    }

    public function setBaseUrl($url) {
        if (strripos($url, 'http://') === false) {
            $url = 'http://' . $url;
        }
        $this->baseUrl = $url;
    }

    /**
     * This method closes the log file and stops the selenium session.
     */
    public function teardown() {
        $this->session->reset();
        $this->logHelper->export();
    }

    /**
     * This method removes all downloaded files from the server and emails the log file
     */
    public function cleanup() {
        if (is_dir($this->cfg->ddir)) {
            $dirhandle = opendir($this->cfg->ddir);
            $this->logHelper->action('Deleting files in  ' . $this->cfg->ddir);
            while (false !== ($file = readdir($dirhandle))) {
                if ($file != '.' && $file != '..') {
                    unlink($this->cfg->ddir . $file);
                }
            }
        }
        $this->logHelper->export();
        if ($this->cfg->emaillogs) {
            $this->logHelper->emailActions();
        }
        $this->session->stop();
    }

    /**
     * Simple function to get the page content into the internal page variable. This also checks for a PLD alert dialog and closes it.
     */
    public function reloadPage($pageTitle = '') {
        $this->page = $this->session->getPage();

        if ($pldDialog = $this->page->find('css', '#local_pld_alert')) {
            if ($button = $pldDialog->findButton('Close')) {
                $this->logHelper->action($pageTitle . ': Closing PLD Alert');
                $button->press();
            }
            $this->page = $this->session->getPage();
        }
    }

    /**
     * Wrapper class for session visit to the url based on the base url.  The base url can be included if already there.
     * The function then grab the page content.
     *
     * @param string $url the url to go to with a / prefix or the full http://basesiteurl
     */
    public function visit($url = '') {
        $themename = 'theme=clean';
        if (strripos($url, $this->baseUrl) === false) {
            $url = $this->baseUrl . $url;
        }
        // Append the theme name in order to use a common theme instead of whatever the admin has set for the site
        if (strripos('?', $url) === false) {
            $url = $url . '?' . $themename;
        } else {
            $url = $url . '&' . $themename;
        }

        $this->logHelper->action('Visiting ' . $url);
        $this->session->visit($url);
        $this->reloadPage();
    }
}
