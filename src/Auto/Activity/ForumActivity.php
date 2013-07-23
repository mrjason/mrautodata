<?php
/**
 * Base forum activity class
 * @package    Activity
 * @subpackage Forum
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

use Auto\Activity\Activity,
    Auto\Discussion;

/**
 * Forum base activity.
 * @todo Add Grading support
 * @todo Add forum rating
 */
class ForumActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'forum';

    /**
     * Interact with the discussion means do the following:
     * <ul>
     * <li>20% of the time create a discussion</li>
     * <li>View each discussion in the forum</li>
     * <li>20% of the time reply to a random post</li>
     * </ul>
     */
    public function interact() {
        $this->c->l->action($this->title . ': Starting interaction');
        $this->view();
        $this->changeDisplayFormat();
        /// 20% of the time create a discussion
        $createDiscussion = !rand(0, 4);
        if ($createDiscussion) {
            $this->createDiscussion();
        } else {
            $this->c->l->action($this->title . ': Skipping discussion creation');
        }
        $discussions = $this->getDiscussions();
        foreach ($discussions as $discussion) {
            $viewDiscussion = !rand(0, 9);
            /// 10% of the time actually view a forum discussion
            if ($viewDiscussion) {
                $discussion->view();
                /// 20% of the time create a random reply to a forum post
                $reply = rand(0, 4);
                if ($reply) {
                    $discussion->randReply();
                }
                $this->view();
            } else {
                $this->c->l->action($this->title . ': Skipping viewing a discussion');
            }
        }
    }

    /**
     * Used to create a discussion.  Creates a new discussion object and then executes the create function.
     */
    public function post() {
        $this->view();
        $this->changeDisplayFormat();
        $discussion = new \Auto\Discussion(array('c' => $this->c));
        $discussion->create();
        $this->view();
    }

    /**
     * This function changes the current view format for advanced forums
     *
     * @param string $label The select label to change the forums view tos
     */
    public function changeDisplayFormat($label = 'Default') {
        if ($el = $this->c->p->findField('displayformat')) {
            $this->c->l->action($el->getValue());
            if ($el->getValue() != $label) {
                $el->selectOption($label);
                $this->c->reloadPage();
            }
        }
    }

    /**
     * Create a new discussion in the forum.
     * @return Discussion The newly created discussion.
     */
    public function createDiscussion() {
        $discussion = new Discussion(array('c' => $this->c));
        $discussion->create();
        return $discussion;
    }

    /**
     * This method gets all forum discussions for a forum
     * @todo Add support for paging
     *
     * @param boolean $paging should this page interact with the Moodle paging system.
     *
     * @return array An array of \Auto\Discussion objects that are on the page or every page is paging is set to true.
     */
    public function getDiscussions($paging = false) {
        $discussions = array();
        /// There is more than one page of forum data
        /*if($el = $this->c->p->find('css','.paging') && $paging){
        }*/
        $ds = $this->c->p->findAll('css', '.forumheaderlist .discussion');
        foreach ($ds as $d) {
            $starter = $d->find('css', '.topic.starter a');
            /// Get the link to access the replies
            /// Urls are used because we mayb leave the page and the object would not stay
            if ($reply = $d->find('css', '.replies a')) {
                $rurl = $reply->getAttribute('href');
            }
            /// Get the link to the author's profile and the author's name.
            if ($author = $d->find('css', '.author a')) {
                $aurl  = $author->getAttribute('href');
                $aname = $author->getText();
            }
            /// Find all of the flags, bookmark and substantitive incase they need to be accessed.
            /// Urls are used because we mayb leave the page and the object would not stay
            $flags = $d->findAll('css', '.author .hsuforum_flags a');
            foreach ($flags as $flag) {
                $furl = $flag->getAttribute('href');
                if (strpos($furl, 'flag=bookmark')) {
                    $fburl = $furl;
                } else {
                    $fsurl = $furl;
                }
            }
            /// Find the url for the subscribe link
            /// Urls are used because we mayb leave the page and the object would not stay
            if ($subscribe = $d->find('css', '.subscribe a')) {
                $surl = $subscribe->getAttribute('href');
            }
            $url = $starter->getAttribute('href');
            $ids = explode('d=', $url);

            $options       = array(
                'c'      => $this->c,
                'author' => isset($aname) ? $aname : '',
                'url'    => $url,
                'aurl'   => isset($aurl) ? $aurl : '',
                'rurl'   => isset($rurl) ? $rurl : '',
                'surl'   => isset($surl) ? $surl : '',
                'fburl'  => isset($fburl) ? $fburl : '',
                'fsurl'  => isset($fsurl) ? $fsurl : '',
                'id'     => $ids[1],
                'title'  => $starter->getText()
            );
            $discussions[] = new \Auto\Discussion($options);
        }

        return $discussions;
    }
}