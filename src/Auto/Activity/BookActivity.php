<?php
/**
 * Book activity class
 *
 * @package Activity
 * @subpackage Book
 * @author Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

use Auto\Activity\Activity;

/**
 * Book activity class
 */
class BookActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'book';

    /**
     * View the book and click the next link until there are no more next links. This will page through chapters.
     */
    public function post(){
        $this->view();
        while($el = $this->c->p->findLink('Next')){
            $this->c->l->action($this->title.': Going to the next chapter of the book');
            $el->click();
            $this->c->reloadPage();
        }
    }
}