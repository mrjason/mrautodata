<?php
/**
 * Glossary activity class
 * @package    Activity
 * @subpackage Glossary
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

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
    public function post() {
        $this->view();

        if ($element = $this->container->page->findButton('Add a new entry')) {
            $element->click();
            $this->container->logHelper->action($this->title . ': Ading glossary entry to ' . $this->title);
            $this->container->reloadPage($this->title);
            if ($concept = $this->container->page->findField('concept')) {
                $concept->setValue($this->container->contentHelper->getRandWord());
            }
            if ($field = $this->container->page->findField('id_definition_editor')) {
                $field->setValue($this->container->contentHelper->getRandSentence('html'));
            }
            if ($keyword = $this->container->page->findField('id_aliases')) {
                $keyword->setValue($this->container->contentHelper->getRandWord());
            }
            if ($button = $this->container->page->findButton('Save changes')) {
                $button->click();
                $this->container->reloadPage($this->title);
            }
        }
    }
}