<?php
/**
 * Activity base class
 *
 * @package Activity
 * @subpackage Assign
 * @author Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
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
    public function post(){
        $this->view();
        $btn = $this->c->p->findButton('Add submission');

        if (!$btn){
            $btn = $this->c->p->findButton('Edit my submission');
        }

        if($btn){
            $btn->press();
            $this->c->reloadPage();
            if($editor = $this->c->p->findField('id_onlinetext_editor')){
                if($editor->isVisible()){
                    $editor->setValue($this->c->ch->getRandEssay('html'));
                } else {
                    $this->c->l->failure($this->title.': id_onlinetext_editor textarea is not visible');
                }
            }
            $this->c->ch->uploadRandFile($this->c->cf->filedir,'math','pdf');
            if($save = $this->c->p->findButton('Save changes')){
                $save->press();
            }
            $this->c->reloadPage();
        } else {
            $this->c->l->action($this->title.': Could not find Add or Edit submission button for '.$this->title);
        }
    }


}