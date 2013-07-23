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
        /// continue quiz attempt
        foreach ($buttons as $button) {
            if ($btn = $this->c->p->findButton($button)) {
                $btn->click();

                $this->c->reloadPage();
                while ($next = $this->c->p->findButton('Next')) {
                    $questions = $this->getQuestions();
                    $this->c->l->action($this->title . ': Answering all questions');
                    foreach ($questions as $question) {
                        $question->answer();

                        if (rand(0, 99) == 0) {
                            $question->flag();
                        }
                    }
                    $next->click();
                    $this->c->reloadPage();
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
        $this->c->l->action($this->title . ': Getting all questions on the quiz page');
        $qs        = $this->c->p->findAll('css', '#responseform .que');
        $questions = array();
        foreach ($qs as $q) {
            $classes   = explode(' ', $q->getAttribute('class'));
            $classname = '\Auto\Question\\' . ucfirst($classes[1] . 'Question');
            if (class_exists($classname)) {
                $options     = array(
                    'c'        => $this->c,
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
        if ($btn = $this->c->p->findButton('Submit all and finish')) {
            $this->c->l->action($this->title . ':Submitting and finishing ' . $this->title);
            $btn->click();
            if ($confirmdlg = $this->c->p->find('css', '#confirmdialog_c')) {
                $confirmdlg->pressButton('Submit all and finish');
                $this->c->l->action($this->title . ':Confirming Submitting and finishing ' . $this->title);
                $this->c->reloadPage();
            }
            if ($continue = $this->c->p->findButton('Continue')) {
                $this->c->l->action($this->title . ':Found continue button for an error not sure why');
                $continue->click();
                $this->c->reloadPage();
            } else {
                while ($next = $this->c->p->findLink('Next')) {
                    $next->click();
                    $this->c->reloadPage();
                }
                if ($finish = $this->c->p->findLink('Finish review')) {
                    $this->c->l->action($this->title . ':Finishing review');
                    $finish->click();
                    $this->c->reloadPage();
                }
            }
        } else {
            $this->c->l->action($this->title . ':Submit and finish button not present');
        }
    }

    /**
     * This function is used to grade manually graded questions in a quiz.
     * @todo   Update to work with mink
     * @access public
     */
    public function grade() {
        $el = $this->c->p->find('css', 'div#quizattemptcounts a');
        $el->click();
        $this->c->reloadPage();
        $this->c->reloadPage();
        $el->click("link=Manual grading");
        $this->c->reloadPage();
        $el->click("//table[@id='attempts']/tbody/tr[2]/td[4]/strong[2]/a");
        $this->c->reloadPage();
        $questions = $this->selenium->getXpathCount("//form/div");
        for ($i = 1; $i < $questions; $i++) {
            $basexpath = "//form/div[$i]/fieldset/div";
            $this->selenium->type($basexpath . "/div[2]/textarea", $this->c->ch->getRandParagraph()); /// comment
            $maxvalues = explode('/', $el->getRandText()); //($basexpath."[2]/div[2]"));
            $this->selenium->type($basexpath . "[2]/div[2]/input", rand(0, (int)$maxvalues[1])); /// grade

            $button = $this->c->p->findButton('Save Changes');
            $button->click();
            $this->c->reloadPage();
        }
    }
}