<?php
/**
 * LogHelper class
 * @package    Helper
 * @subpackage LogHelper
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Helper;

use Symfony\Component\Console\Helper\Helper;

/**
 * This class is designed to script common actions within a Moodle site to generate data similar to a student or teacher's actions.
 */
class LogHelper extends Helper {

    /**
     * @var array Actions recorded as being taken by the process
     */
    protected $actions = array();
    /**
     * @var array Failures that were causes by the process
     */
    protected $failures = array();
    /**
     * @var string The log directory to create files
     */
    protected $dir = '';
    /**
     * @var string|stdClass The site the log should be created for
     */
    protected $site = '';
    /**
     * @var string|Swift_Mailer The SwiftMailer class
     */
    protected $mailer = '';

    /**
     * @var ConfigHelper ConfigHelper class
     */
    protected $cfg;

    /**
     * Load up the Log helper with the directory
     */
    public function init() {
        $this->cfg    = $this->getHelperSet()->get('config');
        $this->dir    = $this->cfg->get('dirs', 'log');
        $transport    = \Swift_SmtpTransport::newInstance($this->cfg->get('mail', 'host'), $this->cfg->get('mail', 'port'), $this->cfg->get('mail', 'secure'))
            ->setUsername($this->cfg->get('mail', 'username'))
            ->setPassword($this->cfg->get('mail', 'password'));
        $this->mailer = \Swift_Mailer::newInstance($transport);
    }

    /**
     * Set the directory that the logs will be stored in
     *
     * @param string $dir Directory that the logs are stored in
     */
    public function setDir($dir = '') {
        if (!is_dir($dir)) {
            $this->dir = $this->cfg->get('dirs', 'log');
        } else {
            $this->dir = $dir;
        }
    }

    /**
     * Set the site to create the logs for.
     *
     * @param stdClass $site Site to set the logs for
     */
    public function setSite($site) {
        $this->site = $site;
        if (isset($this->dir)) {
            $this->site->afile = $this->dir . $site->name . '-actions-' . date('Ymd') . '.txt';
            $this->site->ffile = $this->dir . $site->name . '-failures-' . date('Ymd') . '.txt';
        }
        $this->site->arecorded = '';
        $this->site->frecorded = '';
    }

    /**
     * This method logs the actions either to the screen or to a file.
     * @access public
     *
     * @param string $msg
     */
    public function action($msg) {
        $this->actions[] = date('[Y:m:d H:i] ') . $msg;
        print_r(date('[Y:m:d H:i] ') . $msg . "\n");
    }

    /**
     * This method logs the errors  reported either to the screen or to a file.
     * It is like actions only adds the string Error.
     * @access public
     *
     * @param string $msg the error message to be reported
     */
    public function error($msg) {
        $msg = 'Error: ' . $msg;
        $this->action($msg);
    }

    /**
     * This method logs the failures in the process that need to be reported to an admin.
     * These messages are logged in a separate file from the error and actions.
     * @access public
     *
     * @param string $msg the failure message to be reported
     */
    public function failure($msg) {
        $this->failures[] = date('[Y:m:d H:i] ') . $msg;
    }

    /**
     * Export the log files to the screen a file or return then to be processed
     *
     * @param string $type   plaintext or html for creation
     * @param bool   $return should the logs be returned as a string
     *
     * @return string logs The action logs only no failures
     */
    public function export($type = 'plaintext', $return = false) {
        $flog = '';
        foreach ($this->failures as $error) {
            switch ($type) {
                case 'plaintext':
                    $flog .= $error . "\n";
                    break;
                case 'html':
                    $flog .= $error . '<br />';
                    break;
            }
        }

        $this->site->frecorded .= $flog;

        $this->failures = array();
        if (!$return && !empty($flog)) {
            if (empty($this->site->ffile)) {
                print($flog);
            } else if (!empty($this->site->ffile)) {
                $ffilehandle = fopen($this->site->ffile, 'a');
                if (isset($ffilehandle)) {
                    fwrite($ffilehandle, $flog);
                    fclose($ffilehandle);
                } else {
                    print 'Could not write to group log file ' . $this->site->ffile . "\n";
                }
            }
        }

        $alog = '';
        foreach ($this->actions as $action) {
            switch ($type) {
                case 'plaintext':
                    $alog .= $action . "\n";
                    break;
                case 'html':
                    $alog .= $action . '<br />';
                    break;
            }
        }

        $this->site->arecorded .= $alog;

        $this->actions = array();

        /// Only create an action log if there is data and there is a file
        if (!$return && !empty($alog)) {
            if (empty($this->site->afile)) {
                print($alog);
            } else if (!empty($this->site->afile)) {
                $afilehandle = fopen($this->site->afile, 'a');
                if (isset($afilehandle)) {
                    fwrite($afilehandle, $alog);
                    fclose($afilehandle);
                } else {
                    print 'Could not write to group log file ' . $this->site->afile . "\n";
                }
            }
        } else {
            return ($alog);
        }
    }

    /**
     * Given a site object check if there the site failed.  Failure is defined by a site not having created an action file or having created a failure log file.
     */
    public function failed() {
        if (!file_exists($this->site->afile) || file_exists($this->site->ffile)) {
            return true;
        }

        return false;
    }

    /**
     * Delete the actions log file after we checked for failures
     */
    public function delete() {
        if (file_exists($this->site->afile)) {
            unlink($this->site->afile);
        } else {
            print 'Could not find' . $this->site->afile . " to delete.\n";
        }
    }

    /**
     * Generate the log infomration to an HTML file
     *
     * @param $name Name of the file
     * @param $html The HTML to be stored in the file
     *
     * @author Jason Hardin <jason@moodlerooms.com>
     */
    public function genHTML($name, $html) {
        $filehandle = fopen($this->dir . $name, 'a');
        if (isset($filehandle)) {
            fwrite($filehandle, $html);
            fclose($filehandle);
        }
    }

    /**
     * Email the action log to the user for the site and the process admins
     */
    public function emailActions() {
        $to       = array();
        $filename = '';

        if ($this->site->sendemail) {
            $subject = 'Nightly Data Generation Action Log For ' . $this->site->url . ' Ran on ' . date('l F j, Y');
            $text    = 'This is the Moodlerooms nightly data generation action log for ' . $this->site->url . ' that ran on ' . date('l F j, Y') . ".\n";

            /// Always email the script admin
            $address        = new \stdClass();
            $address->email = 'jason@moodlerooms.com';
            $address->name  = 'Jason Hardin';
            $to[]           = $address;

            if (isset($this->site->owner)) {
                $address        = new \stdClass();
                $address->email = $this->site->owner['email'];
                $address->name  = $this->site->owner['name'];
                $to[]           = $address;
            }

            if (in_array('sales', $this->site->type)) {
                $address        = new \stdClass();
                $address->email = 'abraden@moodlerooms.com';
                $address->name  = 'Andy Braden';
                $to[]           = $address;
            }

            if (file_exists($this->site->afile)) {
                $this->export();
                $filename = $this->site->afile;
            } else {
                $text .= '<br />' . preg_replace('/\n/', '<br />', $this->site->recorded) . $this->export('html', true);
            }
        }
        if (isset($subject) && isset($text) && isset($to)) {
            $this->send($subject, $text, $to, $filename);
        }
    }

    /**
     * Send the email
     *
     * @param        $subject  Email subject
     * @param        $text     The email body
     * @param        $to       The email address the email is to
     * @param string $filename An attachment
     */
    public function send($subject, $text, $to, $filename = '') {
        foreach ($to as $address) {
            if (isset($address->name)) {
                $addresses[$address->email] = $address->name;
            } else {
                $addresses[] = $address->email;
            }
        }

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array('jason@moodlerooms.com' => 'Jason Hardin'))
            ->setTo($addresses)
            ->setBody($text, 'text/html')
            ->addPart($text, 'text/plain');

        if (!empty($filename) && file_exists($filename)) {
            $message->attach(\Swift_Attachment::fromPath($filename));
        }
        if ($result = $this->mailer->send($message)) {
            echo "Email Message Sent\n";
        } else {
            echo "Email Message Failed\n";
        }
    }

    /**
     * Returns the canonical name of this helper.
     * @return string The canonical name
     * @api
     */
    public function getName() {
        return 'log';
    }
}

?>
