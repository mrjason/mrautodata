<?php
/**
 * Cpntainer class
 *
 * @package Container
 * @author Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto;

use Behat\Mink\Session,
    Behat\Mink\Driver\ZombieDriver,
    Behat\Mink\Driver\SahiDriver,
    Behat\Mink\Driver\Selenium2Driver;

/**
 * A Container class to contain all of the variables that are used by the majority of the other classes in Auto.
 */
class Container
{
    /**
     * @var ConfigHelper The server configuration object
     */
    public $cf;
    /**
     * @var \Behat\Mink\Session The Mink Session object.
     */
    public $s;
    /**
     * @var object The Mink object returned from session->getPage()
     */
    public $p;
    /**
     * @var LogHelper LogHelper object to access that class's functions.
     */
    public $l;
    /**
     * @var string The base url for the site and session.
     */
    public $burl;

    /**
     * Constructor for the Container object
     *
     * @param ConfigHelper $cfg ConfigHelper object to grab all of the server settings.
     * @param LogHelper $l Create a new LogHelper object and pass it here
     * @param ContentHelper $ch ContentHelper object
     */
    public function __construct($cfg, $ch, $l){
        $this->cf->delay = $cfg->get('server','delay');
        $this->cf->driver = $cfg->get('server','driver');
        $this->cf->verbose = $cfg->get('notifications','verbose');
        $this->cf->emaillogs = $cfg->get('notifications','emaillogs');
        $this->cf->filedir = $cfg->get('dirs','file');
        $this->cf->ddir = $cfg->get('dirs','download');
        $this->ch = $ch;
        $this->l =  $l;
        $this->l->init();

        try {
            switch($this->cf->driver){
                case 'zombie':
                    $driver = new ZombieDriver('127.0.0.1',8124);
                    break;
                case 'sahi':
                    $driver = new SahiDriver('firefox'); //This doesn't seem to work and hasn't been updated since 2011
                    break;
                default:
                    $driver = new Selenium2Driver();
                    break;

            }
            $this->s = new Session($driver);
            $this->s->start();
            $this->ch->setup($this);
        } catch(Exception $e) {
            $this->l->action($e->msg);
            $this->cleanup();
        }
    }

    public function setBaseUrl($url){
        if(strripos($url,'http://' ) === false){
            $url = 'http://'.$url;
        }
        $this->burl = $url;
    }

    /**
     * This method closes the log file and stops the selenium session.
     */
    public function teardown(){
        $this->s->reset();
        $this->l->export();
    }

    /**
     * This method removes all downloaded files from the server and emails the log file
     */
    public function cleanup(){
        if(is_dir($this->cf->ddir)){
            $dirhandle = opendir($this->cf->ddir);
            $this->l->action('Deleting files in  '.$this->cf->ddir);
            while (false !== ($file = readdir($dirhandle))) {
                if($file != '.' && $file != '..'){
                    unlink($this->cf->ddir.$file);
                }
            }
        }
        $this->l->export();
        if($this->cf->emaillogs){
            $this->l->emailActions();
        }
        $this->s->stop();
    }

    /**
     * Simple function to get the page content into the internal page variable. This also checks for a PLD alert dialog and closes it.
     */
    public function reloadPage(){
        $this->p = $this->s->getPage();

        if($plddlg = $this->p->find('css','#local_pld_alert')){
            $this->p->pressButton('Close');
            $this->p = $this->s->getPage();
        }
        //$this->l->action('Current url is '.$this->s->getCurrentUrl());
    }

    /**
     * Wrapper class for session visit to the url based on the base url.  The base url can be included if already there.
     * The function then grab the page content.
     *
     * @param string $url the url to go to with a / prefix or the full http://base sit eurl
     */
    public function visit ($url=''){
        if(strripos($url,$this->burl ) === false){
            $url = $this->burl.$url;
        }
        $this->l->action('Visiting '.$url);
        $this->s->visit($url);
        $this->reloadPage();

/* Debugging info
        $replace = array($this->burl,'/', '.php');
        $with = array('','','.html');
        $file = str_replace($replace, $with,$url);
        if(empty($file)){
            $file = 'baseurl.html';
        }
        $this->l->genHTML($file,$this->s->getPage()->getContent());
*/
    }
}
