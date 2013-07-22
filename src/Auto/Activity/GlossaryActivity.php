<?php
/**
 * Glossary activity class
 *
 * @package Activity
 * @subpackage Glossary
 * @author Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

use Auto\Activity\Activity;

/**
 * Glossary activity class
 */
class GlossaryActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'glossary';

    /**
     * View the gloassary and then create a new entry based on a random word.
     */
    public function post(){
        $this->view();

        if($el = $this->c->p->findButton('Add a new entry')){
            $el->click();
            $this->c->l->action($this->title.': Ading glossary entry to '.$this->title);
            $this->c->reloadPage();
            if($concept = $this->c->p->findField('concept')){
                $concept->setValue($this->c->ch->getRandWord());
            }
            if($field = $this->c->p->findField('id_definition_editor')){
                $field->setValue($this->c->ch->getRandSentence('html'));
            }
            if($keyword = $this->c->p->findField('id_aliases')){
                $keyword->setValue($this->c->ch->getRandWord());
            }
            if($button = $this->c->p->findButton('Save changes')){
                $button->click();
                $this->c->reloadPage();
            }
        }
    }
}