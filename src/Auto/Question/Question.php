<?php
/**
 * Question base class
 * To provide the answer to a question use the following HTML in the question text. Replace answervalue with the actual answer to the question.
 * <div class="mrqueanswer">answervalue</div>
 * This code should be placed into each question in match question types.
 * @package   Question
 * @author    Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Question;

/**
 * Base class for all Quiz question types.  This denotes all of the functions that are mandatory for a question.
 */
class Question {
    /**
     * @var Container containing all variables for the session, page, log helper and content helper.
     */
    protected $container;

    /**
     * @var \Behat\Mink\NodeElement Mink object for the question area.
     */
    protected $qdiv;

    /**
     * @var array Array of classes associated with the question area
     */
    protected $qclasses;

    /**
     * @var string|array Answer to be set for the question.
     */
    protected $answer;

    /**
     * @var \Behat\Mink\NodeElement Mink object for field used to answer the question.
     */
    protected $field;
    /**
     * @var \Behat\Mink\NodeElement Mink object for the flag button
     */
    protected $fbtn;

    /**
     * @var \Behat\Mink\NodeElement Mink object for the check button
     */
    protected $containerbtn;

    /**
     * @var string The title of the question.
     */
    protected $title;

    /**
     * Constructor for Question
     *
     * @param $options mixed And array of variables that will be set for the question.  Must include the container C variable to work.  Array should be array('variable name'=> $value).
     */
    public function __construct($options) {
        foreach ($options as $title => $value) {
            $this->{$title} = $value;
        }

        if (!isset($this->title)) {
            $this->getTitle();
        }

        if (!isset($this->fbtn)) {
            $this->getFlag();
        }

        if (!isset($this->containerbtn)) {
            $this->getCheckButton();
        }

        $this->answerSetup();
    }

    public function answerSetup() {
        if (!isset($this->answer)) {
            if (!$this->getAnswer()) {
                $this->getRandomAnswer();
            }
        }

        if (!isset($this->field)) {
            $this->getAnswerField();
        }
    }

    /**
     * Flag the question using the flag button
     */
    public function flag() {
        if (!isset($this->fbtn)) {
            $this->getFlag();
        }
        if (isset($this->fbtn)) {
            $this->fbtn->click();
        }
    }

    /**
     * Press the check button if it exists for the question.
     */
    public function check() {
        if (!isset($this->containerbtn)) {
            $this->getCheckButon();
        }
        if (isset($this->containerbtn)) {
            $this->containerbtn->press();
            $this->reloadPage();
        }
    }

    /**
     * Get the flag Mink object from within the question.  Set it internally and return it.  Return false if it can't be found.
     * @return bool|\Behat\Mink\NodeElement Locate the flag field in the question and set that in the object and return it. Return false if it doesn't exist.
     */
    public function getFlag() {
        if ($flag = $this->qdiv->find('css', '.questionflagimage')) {
            $this->fbtn = $flag;
            return $this->fbtn;
        }
        return false;
    }

    /**
     * Get the answer value that has been added to the question text or return false if it can't be found.
     * <div class="queanswer hide">answer</div>
     * @return bool  Return false if the answer doesn't exist doesn't exist.
     */
    public function getAnswer() {
        if ($answer = $this->qdiv->find('css', '.queanswer')) {
            $this->answer = $answer->getHTML();
            $this->container->logHelper->action($this->title . ': Found queanswer set to ' . $this->answer);
            return true;
        }
        return false;
    }

    public function getRandomAnswer() {
    }

    /**
     * Get the Mink object for the check button to check the answer if it exists on the page. Return false if it doesn't exist.
     * @return bool|\Behat\Mink\Element Locate the check button in the question and set that in the object and return it. Return false if it doesn't exist.
     */
    public function getCheckButton() {
        if ($button = $this->qdiv->findButton('Check')) {
            $this->containerbtn = $button;
            return $this->containerbtn;
        }
        return false;
    }

    /**
     * Get the title of the question, this is mostly the question number. Return false if it can't be found.
     * @return bool|\Behat\Mink\NodeElement Locate the question title in the question and set that in the object and return it. Return false if it doesn't exist.
     */
    public function getTitle() {
        if ($element = $this->qdiv->find('css', '.info .no')) {
            $this->title = $element->getText();
            return $this->title;
        }
        return false;
    }

    /**
     *  Answer the question in the form using the field value.
     */
    public function answerCorrect() {
        $this->answer();
    }

    public function answerRandomly() {
        $this->getRandomAnswer();
        $this->answer();
    }

    /**
     * Return the field that will be used to answer the question
     * @return bool|\Behat\Mink\Element The field to put the answer in.
     */
    public function getAnswerField() {
    }
}
