<?php
/**
 * ContentHelper class
 * @package    Helper
 * @subpackage ContentHelper
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Helper;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Yaml\Parser;

/**
 * ContentHelper class for interactiosn with content that need to be generated to the screen.  Files and strings.
 */
class ContentHelper extends Helper {
    protected $lang;
    protected $content;
    protected $container;

    /**
     * @var array HTML attributes that are associated with each type of information array for when the information is returned as HTML
     */
    protected $html = array(
        'paragraph'      => 'p',
        'sentence'       => 'p',
        'question'       => 'p',
        'header'         => 'h2',
        'teacherComment' => 'p',
        'word'           => 'p'
    );
    /**
     * @var array Creole Moodle wiki wrappers for each type of content arrays that are returned.
     */
    protected $containercreole = array(
        'paragraph' => '\\\\',
        'sentence'  => '\\\\',
        'question'  => '\\\\',
        'header'    => '='
    );

    /**
     * Setup the helper because construct is called with no variables early on in the Application processing and configuration
     *
     * @param \Auto\Container $c     configured container to access logs and mink script
     * @param                 $lang  string
     * @param                 string lang Language to load for content
     */
    public function setUp($c) {
        $this->container = $c;
        $yaml            = new Parser();
        try {
            $this->content = $yaml->parse(file_get_contents(__DIR__ . '/../Resources/Yaml/Lang/' . $c->cfg->lang . '/Content.yml'));
        } catch (ParseException $e) {
            $this->content = $yaml->parse(file_get_contents(__DIR__ . '/../Resources/Yaml/Lang/EN/Content.yml'));
        }
    }

    /**
     * Generate a random essay
     *
     * @param string $output plaintext or html for the return type
     *
     * @return string HTML or plaintext essay
     */
    public function getRandEssay($output = 'plaintext') {
        return $this->getRandHeader($output) . $this->getRandParagraph($output) . $this->getRandParagraph($output) . $this->getRandParagraph($output) . $this->getRandParagraph($output);
    }

    /**
     * Generate a random paragraph of data
     *
     * @param string $output plaintext, html or creole  the return type
     *
     * @return string
     */
    public function getRandParagraph($output = 'plaintext') {
        return $this->getRandText('paragraph', $output);
    }

    /**
     * Generate a random question
     *
     * @param string $output plaintext, html or creole for the return type
     *
     * @return string
     */
    public function getRandQuestion($output = 'plaintext') {
        return $this->getRandText('question', $output);
    }

    /**
     * Generate a random header
     *
     * @param string $output plaintext, html or creole for the return type
     *
     * @return string
     */
    public function getRandHeader($output = 'plaintext') {
        return $this->getRandText('header', $output);
    }

    /**
     * Generate a random sentence
     *
     * @param string $output plaintext, html or creole for the return type
     *
     * @return string
     */
    public function getRandSentence($output = 'plaintext') {
        return $this->getRandText('sentence', $output);
    }

    /**
     * Generate a random word
     *
     * @param string $output plaintext, html or creole for the return type
     *
     * @return string
     */
    public function getRandWord($output = 'plaintext') {
        return $this->getRandText('word', $output);
    }

    public function getRandTeacherComment($output = 'plaintext') {
        return $this->getRandText('teacherComment', $output);
    }

    /**
     * Create random text from the arrays
     *
     * @param $type   string The type of text to create word, sentence, header, paragraph, or question
     * @param $output string One of three types plaintext, html or creole for the Moodle wiki.
     *
     * @return string
     */
    public function getRandText($type, $output) {
        $randnum = rand(0, (count($this->content[$type]) - 1));

        $text = $this->content[$type][$randnum];

        if ($output == 'html') {
            $tag  = $this->html[$type];
            $text = "<$tag>$text</$tag>";
        } else if ($output == 'creole') {
            $tag  = $this->html[$type];
            $text = "$tag $text $tag ";
        }

        return $text;
    }

    /**
     * Create a wiki body post in creole
     * @return string Wiki post
     */
    public function getWikiBody() {
        $wikibody = $this->getRandHeader('creole') . $this->getRandParagraph('creole');

        foreach ($this->content['header'] as $header) {
            $wikibody .= "[[$header]]\\\\";
        }

        return $wikibody;
    }

    /**
     * Return a random file of the extention in the directory and type provided. Intended for the included files directory
     *
     * @param $dir  The base directory where the files are stored, usually $this->container->logHelper->fdir
     * @param $type The type of file to be getting, math, english, scorm, imscp
     * @param $ext  The file's extension also a directory, pdf, zip, docx
     *
     * @return string|bool
     */
    public function getRandFile($dir, $type, $ext) {
        $filedir = $dir . $type . '/' . $ext . '/';
        if (is_dir($filedir)) {
            $files  = scandir($filedir, 1);
            $picked = rand(0, (count($files) - 3));
            return $filedir . $files[$picked];
        } else {
            return false;
        }
    }

    /**
     * Create a random name to save the file as in the repository browser
     * @return string Random file name
     */
    public function getFilename() {
        return '-rev' . rand(2, 9999999999);
    }

    /**
     * Grab a file from the sent directory and then upload it to Moodle via the upload file repository
     *
     * @param        $dir    The base directory where the files are stored, usually $this->container->logHelper->fdir
     * @param        $type   The type of file to be getting, math, english, scorm, imscp
     * @param        $ext    The file's extension also a directory, pdf, zip, docx
     * @param string $saveas The name to save the file as
     */
    public function uploadRandFile($dir, $type, $ext, $saveas = '') {
        if ($filename = $this->getRandFile($dir, $type, $ext)) {
            $this->addFile($filename, $saveas);
        }
    }

    /**
     * Click on the add file link in the file browser or the choose a file button for scorm and imscp. Then execute the file upload repository.
     * TODO: Add support for other Moodle repostiories like server files, or recent files.
     *
     * @param        $file   The full path to the file to be uploaded
     * @param string $saveas the name of the file to be saved as
     */
    public function addFile($file, $saveas = '') {
        if (file_exists($file)) {
            $element = 0;
            $this->container->reloadPage();
            if ($div = $this->container->page->find('css', '.fp-btn-add')) {
                sleep($this->container->cfg->delay); // it seems some javascript is running to update the file manager.  The add button can be found and then hidden when the .fm-maxfiles class is applied.
                /// There is a class added to hide the add button when the maximum allowed files is reached.
                if ($max = $this->container->page->find('css', '.filemanager.fm-maxfiles')) {
                    $this->container->logHelper->action('Maximum files have been uploaded');
                    $element = 0;
                } else {
                    $this->container->logHelper->action('Found the fp-btn-add button');
                    $element = $div->find('css', 'a');
                }
            } else if ($element = $this->container->page->findButton('Choose a file...')) {
                sleep($this->container->cfg->delay);
                $this->container->logHelper->action('Found the Choose a file... button');
            }
            if (!empty($element)) {
                $element->click();
                if ($repoarea = $this->container->page->find('css', '.fp-list')) {
                    if ($uploadrepo = $repoarea->findLink('Upload a file')) {
                        $uploadrepo->click();
                        /// Need to delay looking for AJAX to process and part of the page to be unhidden or added.
                        sleep($this->container->cfg->delay);
                        if ($upload = $this->container->page->findField('repo_upload_file')) {
                            $this->container->logHelper->action('Attaching file ' . $file . ' in upload repository');
                            $upload->attachFile($file);

                            if (!empty($saveas)) {
                                $element = $this->container->page->findField('title');
                                $element->setValue($saveas);
                            }
                            $button = $this->container->page->findButton('Upload this file');
                            try {
                                $button->press();
                                $this->container->logHelper->action('Clicked the Upload this File button');
                            } catch (Exception $e) {
                                //do nothing because the likely issue is an alert that we can't handle.
                            }
                        } else {
                            $this->container->logHelper->action('Could not find .fp-upload-form in repository browser');
                        }
                    } else {
                        $this->container->logHelper->action('Could not find the upload a file link in repository browser');
                    }
                } else {
                    $this->container->logHelper->action('Could not find .fp-list in repository browser');
                }
            } else {
                $this->container->logHelper->action('Could not find the filepicker add button or choose a file button');
            }
        } else {
            $this->container->logHelper->action($file . ' Does not exist on the computer');
        }
    }

    /**
     * Returns the canonical name of this helper.
     * @return string The canonical name
     * @api
     */
    public function getName() {
        return 'content';
    }
}

?>