<?php
/**
 * Quiz activity class
 * @package    Activity
 * @subpackage Quiz
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

use Auto\Activity\Activity;

/**
 * Quiz activity class.
 */
class QuizActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'quiz';

    /**
     * This function is used to attempt a Quiz
     * An attempt performs the following:
     * <ul>
     * <li>Answers the question</li>
     * <li>10% of the time flags the question</li>
     * <li>Clicks the next button</li>
     * </ul>
     * @access public
     */
    public function post() {
        $this->view();
        $buttons = array(
            'Attempt quiz now', /// attempt quiz
            'Continue the last attempt',
            'Re-attempt quiz'
        );

        $grade = rand(50,100);
        /// continue quiz attempt
        foreach ($buttons as $button) {
            if ($button = $this->container->page->findButton($button)) {
                $button->click();

                $this->container->reloadPage($this->title);
                while ($next = $this->container->page->findButton('Next')) {
                    $questions = $this->getQuestions();
                    $this->container->logHelper->action($this->title . ': Answering all questions');

                    foreach ($questions as $question) {
                        $rand = rand(0, 100);
                        if($rand <= $grade){
                            $question->answerCorrect();
                        } else {
                            $question->answerRandomly();
                        }

                        if (rand(0, 99) == 0) {
                            $question->flag();
                        }
                    }
                    $next->click();
                    $this->container->reloadPage($this->title);
                }
                $this->finishAttempt();
            }
        }
    }

    /**
     * Get all question areas on the page and return the NodeElement for them in an array.
     * @return array An array of \Behat\Mink\Element objects for the questions on that page.
     */
    public function getQuestions() {
        $this->container->logHelper->action($this->title . ': Getting all questions on the quiz page');
        $qs        = $this->container->page->findAll('css', '#responseform .que');
        $questions = array();
        foreach ($qs as $q) {
            $classes   = explode(' ', $q->getAttribute('class'));
            $classname = '\Auto\Question\\' . ucfirst($classes[1] . 'Question');
            if (class_exists($classname)) {
                $options     = array(
                    'container'        => $this->container,
                    'qdiv'     => $q,
                    'qclasses' => $classes
                );
                $questions[] = new $classname($options);
            }
        }

        return $questions;
    }

    /**
     * This function is used to finalize a quiz attempt. Clicking on the submit and finish button and the modal.
     * @access public
     */
    public function finishAttempt() {
        if ($button = $this->container->page->findButton('Submit all and finish')) {
            $this->container->logHelper->action($this->title . ': Submitting and finishing');
            $button->click();
            if ($confirmdlg = $this->container->page->find('css', '#confirmdialog_c')) {
                $confirmdlg->pressButton('Submit all and finish');
                $this->container->logHelper->action($this->title . ': Confirming Submitting and finishing');
                $this->container->reloadPage($this->title);
            }
            if ($continue = $this->container->page->findButton('Continue')) {
                $this->container->logHelper->action($this->title . ': Found continue button for an error not sure why');
                $continue->click();
                $this->container->reloadPage($this->title);
            } else {
                while ($next = $this->container->page->findLink('Next')) {
                    $next->click();
                    $this->container->reloadPage($this->title);
                }
                if ($finish = $this->container->page->findLink('Finish review')) {
                    $this->container->logHelper->action($this->title . ': Finishing review');
                    $finish->click();
                    $this->container->reloadPage($this->title);
                }
            }
        } else {
            $this->container->logHelper->action($this->title . ': Submit and finish button not present');
        }
    }

    public function teacherInteract($grade){
        /// This should point to grade
    }

    /**
     * This function is used to grade manually graded questions in a quiz.
     * @todo   Update to work with mink
     * @access public
     */
    /*public function grade() {
               $element = $this->container->page->find('css', 'div#quizattemptcounts a');
               $element->click();
               $this->container->reloadPage($this->title);
               $link = $this->container->page->findLink('Manual grading');
               $link->click();
               $this->container->reloadPage($this->title);
               $this->container->page->find('css','table#attempts');
               $element->click("//table[@id='attempts']/tbody/tr[2]/td[4]/strong[2]/a");
               $this->container->reloadPage($this->title);
               $questions = $this->selenium->getXpathCount("//form/div");
               for ($i = 1; $i < $questions; $i++) {
                   $basexpath = "//form/div[$i]/fieldset/div";
                   $this->selenium->type($basexpath . "/div[2]/textarea", $this->container->contentHelper->getRandParagraph()); /// comment
                   $maxvalues = explode('/', $element->getRandText()); //($basexpath."[2]/div[2]"));
                   $this->selenium->type($basexpath . "[2]/div[2]/input", rand(0, (int)$maxvalues[1])); /// grade

                   $button = $this->container->page->findButton('Save Changes');
                   $button->click();
                   $this->container->reloadPage($this->title);
               }
    }*/
}