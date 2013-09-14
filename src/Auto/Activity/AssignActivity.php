<?php
/**
 * Activity base class
 * @package    Activity
 * @subpackage Assign
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

use Auto\Activity\Activity;

/**
 * Moodle 2.3 assignment activity.
 * @todo Add grading support
 */
class AssignActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'assign';

    /**
     * Post to an activity based on what is supported on the page, online submission and file submission supported only.
     */
    public function post() {
        $this->view();
        $buttonName = 'Add submission';
        $button = $this->container->page->findButton($buttonName);

        if (!$button) {
            $buttonName = 'Edit my submission';
            $button = $this->container->page->findButton($buttonName);
        }

        if ($button) {
            $button->press();
            $this->container->logHelper->action($this->title . ': Clicked button '.$buttonName);
            $this->container->reloadPage($this->title);
            if ($editor = $this->container->page->findField('id_onlinetext_editor')) {
                if ($editor->isVisible()) {
                    $editor->setValue($this->container->contentHelper->getRandEssay('html'));
                } else {
                    $this->container->logHelper->failure($this->title . ': id_onlinetext_editor textarea is not visible');
                }
            }
            $this->container->contentHelper->uploadRandFile($this->container->cfg->filedir, 'math', 'pdf');
            if ($save = $this->container->page->findButton('Save changes')) {
                $save->press();
            }
            $this->container->reloadPage($this->title);
        } else {
            $this->container->logHelper->action($this->title . ': Could not find Add or Edit submission button');
        }
    }

    public function teacherInteract($grade){
        $this->view();
        $this->grade($grade);
    }

    public function grade($grade){
        if($link = $this->container->page->find('css','.submissionlinks > a')){
            $link->click();
            $this->container->logHelper->action($this->title . ': Viewing grading and submissions screen');
            $this->container->reloadPage($this->title);
            $gradeButtons = $this->container->page->findAll('css', 'td.cell.c5 > a');
            if(!empty($gradeButtons[0])){
                $gradeButtons[0]->click();
                $this->container->logHelper->action($this->title . ': Navigating to grade first student');
                $this->container->reloadPage($this->title);

                $next = true;
                while($next){
                    if($field = $this->container->page->findField('grade')){ // standard grading
                        $this->container->logHelper->action($this->title . ': Grading standard way');
                        $label = $this->container->page->find('css','#fitem_id_grade .fitemtitle > label');
                        $maxGrade = str_replace('Grade out of ', '', $label->getText());
                        $grade = rand(0,$maxGrade);
                        $field->setValue((string)$grade);
                        $this->container->logHelper->action($this->title . ': Setting grade to '.$grade);
                    } else if ($gradeform = $this->container->page->find('css', '#rubric-advancedgrading')) { /// Rubric grading
                        $this->container->logHelper->action($this->title . ': Grading with rubric grading form');
                        $criterion = $gradeform->findAll('css','.criterion');
                        foreach($criterion as $criteria){
                            $levels = $criteria->findAll('css','.level');
                            $count = count($levels)-1;
                            $selectedLevel = rand(0,$count);
                            $levels[$selectedLevel]->click();
                            $this->container->logHelper->action($this->title . ': Clicking level '.$selectedLevel);
                        }
                        $remarks = $gradeform->findAll('css','.remark textarea');
                        $this->leaveRemarks($remarks);
                    } else if ($gradeform = $this->container->page->find('css', '#checklist-advancedgrading')) { /// Checklist grading
                        $this->container->logHelper->action($this->title . ': Grading with checklist grading form');
                        $items = $gradeform->findAll('css','.item');
                        foreach ($items as $item) {
                            if(rand(0,1)){
                                $item->click();
                            }
                        }
                        $remarks = $gradeform->findAll('css','.remark textarea');
                        $this->leaveRemarks($remarks);
                    } else if($gradeform = $this->container->page->find('css', '#guide-advancedgrading')) { /// marking guide'
                        $this->container->logHelper->action($this->title . ': Grading with marking guide grading form');
                        $scores = $gradingform->findAll('css','.score');
                        foreach($scores as $score){
                            $maxGrade = $score->find('css','.criteriondescriptionscore');
                            $grade = rand(0,$maxGrade->getText());
                            $field = $score->findField('text');
                            $field->setValue((string)$grade);
                            $this->container->logHelper->action($this->title . ': Entering grade '.$grade);
                        }
                        $remarks = $gradeform->findAll('css','.remark textarea');
                        $this->leaveRemarks($remarks);
                    }

                    if (($editor = $this->container->page->findField('id_assignfeedbackcomments_editor')) && rand(0, 10) == 0) {
                        if ($editor->isVisible()) {
                            $editor->setValue($this->container->contentHelper->getRandTeacherComment('html'));
                            $this->container->logHelper->action($this->title . ': Leaving text feedback');
                        } else {
                            $this->container->logHelper->failure($this->title . ': id_assignfeedbackcomments_editor textarea is
                            not visible');
                        }
                    }

                    if (($fileFeedback = $this->container->page->find('css','#header_file')) && rand(0, 50) == 0) {
                        $this->container->logHelper->action($this->title . ': Leaving file feedback');
                        $this->container->contentHelper->uploadRandFile($this->container->cfg->filedir, 'math', 'pdf');
                    }

                    if($button = $this->container->page->findButton('Save and show next')){
                        $this->container->reloadPage($this->title);
                    } else {
                        $next   = false;
                        $button = $this->container->page->findButton('Save changes');
                    }
                    $button->click();
                    $this->container->reloadPage($this->title);
                }
            }
        }
    }

    public function leaveRemarks($remarks){
        foreach($remarks as $remark){
            if(rand(0,10) == 0){
                $this->container->logHelper->action($this->title . ': Leaving a remark');
                $remark->setValue($this->container->contentHelper->getRandTeacherComment());
            }
        }
    }

}