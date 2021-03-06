<?php
/**
 * Discussion class file
 * @package   Discussion
 * @author    Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto;

/**
 * Base discussion class for forum discussion
 */
class Discussion {

    /**
     * @var string The name of the author of the discussion
     */
    protected $author;
    /**
     * @var string id for the discussion used in the Moodle database
     */
    protected $id;
    /**
     * @var string The title of the discussion
     */
    protected $title;
    /**
     * @var Container containing all variables for the session, page, log helper and content helper.
     */
    protected $container;
    /**
     * @var string The url to the discussion view page
     */
    protected $url;
    /**
     * @var string The url to the author's profile page
     */
    protected $authorUrl;
    /**
     * @var string The url to the replies field
     */
    protected $replyUrl;
    /**
     * @var string The url to the subscribe or unsubscribe link
     */
    protected $subscribeUrl;
    /**
     * @var string The url to mark or unmark a discussion as substantitive
     */
    protected $flagSubstantiveUrl;
    /**
     * @var string The url to bookmark or unbookmark the discussion
     */
    protected $flagBookmarkUrl;

    /**
     * Consturctor for the class
     *
     * @param $options mixed And array of variables that will be set for the discussion.  Must include the Container c variable to work.  Array should be array('variable name'=> $value).
     */
    public function __construct($options) {
        foreach ($options as $title => $value) {
            $this->{$title} = $value;
        }
    }

    /**
     * View the discussion within a forum
     */
    public function view() {
        $this->container->logHelper->action($this->title . ': Viewing discussion');
        $this->container->visit($this->url);
    }

    /**
     * This method creates a discussion in any Moodle advanced or regular forum.
     * @access public
     *
     * @param string $type    the type of subject for the post, options are manual, sentence, question and reply.
     * @param string $subject for a manual type this is the subject to be used.
     */
    public function create($type = 'sentence', $subject = '') {
        if ($button = $this->container->page->findButton('Add a new discussion topic')) { /// Standard
            $msg = 'Creating discussion for a standard forum';
        } else if ($button = $this->container->page->findButton('Add a new question')) { /// QA forum
            if ($type != 'manual') {
                $type = 'question';
            }
            $msg = 'Creating discussion for a QA forum type';
        } else if ($button = $this->container->page->findButton('Add a new topic')) { /// news or blog forum
            $msg = 'Creating news or blog forum topic';
        }
        if (!empty($button)) {
            $this->container->logHelper->action($msg);
            $button->click();
            $this->container->reloadPage($this->title);
            $this->post($type, $subject);
        } else {
            $this->container->logHelper->action($this->title . ': Could not find any discussion button to click on');
        }
    }

    /**
     * This function is used to create a new post or reply to a post once the user is within the discussion.
     * This is generally used as an internal method, but can be called externally too.
     * @access public
     *
     * @param string $type the type of subject for the post, options are manual, sentence, question and reply.
     * @param string $text for a manual type this is the subject to be used.
     */
    public function post($type = 'sentence', $text = '') {
        $this->container->logHelper->action('Creating post');
        if ($subject = $this->container->page->findField('id_subject')) {
            switch ($type) {
                case 'sentence':
                    $subject->setValue($this->container->contentHelper->getRandQuestion($type));
                    break;
                case 'question':
                    $subject->setValue($this->container->contentHelper->getRandSentence($type));
                    break;
                case 'manual':
                    $subject->setValue($text);
                    break;
            }
        }
        if ($message = $this->container->page->find('css', '#id_message')) {
            if ($message->isVisible()) {
                $message->setValue($this->container->contentHelper->getRandParagraph());
            } else {
                $this->container->logHelper->error($this->title . ': id_message textarea is not visible');
            }
        }
        if ($select = $this->container->page->findField('subscribe')) {
            $select->selectOption('0');
        }
        /// Randomly add a file to the file area. Should happen 10% of the time.
        if (rand(0, 9) == 0) {
            $this->container->contentHelper->uploadRandFile($this->container->cfg->filedir, 'english', 'pdf');
        }
        if ($button = $this->container->page->findButton('id_submitbutton')) {
            $button->click();
            $this->container->reloadPage($this->title);
        }
        if ($continue = $this->container->page->findLink('Continue')) {
            $continue->click();
            $this->container->reloadPage($this->title);
        }
    }

    /**
     * This function is used to reply to the first post in a discussion
     * @access public
     */
    public function randReply() {
        if ($posts = $this->container->page->findAll('css', 'div.forumpost')) {
            $rand = rand(0, (count($posts) - 1));
            $this->container->logHelper->action($this->title . ': Replying to the ' . $rand . ' reply link');
            if ($reply = $posts[$rand]->findLink('Reply')) {
                $reply->click();
                $this->container->reloadPage($this->title);
                $this->post('reply');
            } else {
                $this->container->logHelper->action($this->title . ': Could not find a reply link');
            }
        } else {
            $this->container->logHelper->action($this->title . ': Could not find posts');
        }
    }

    /**
     * This function is used to rate one or all discussion in a forum.  It works with discussions that have multiple pages of posts.  This is a long running process.
     * @todo   make this work with Mink
     * @access public
     *
     * @param string $postLink
     */
    public function rate($postLink = 'all') {
        $links = array();

        $next = 1;
        while ($next) {
            if ($postLink == 'all') {
                $discussionslink = "//div[@id='region-content']/table/tbody/tr";
                $discussions     = $this->selenium->getXpathCount($discussionslink);
                for ($i = 0; $i < $discussions; $i++) {
                    $links[] = $discussionslink . "[$i]/td[1]/a";
                }
            } else {
                $links[] = 'link=' . $postLink;
                $next    = false;
            }

            foreach ($links as $id => $link) {
                /// Moodle doesn't return to the paged view when you click on the forum link in the nav bar so we need to next to the page we were on.
                for ($j = 1; $j < $next; $j++) {
                    if ($element = $this->container->page->find("link=Next")) {
                        $element->click("link=Next");
                        $this->container->reloadPage($this->title);
                    }
                }
                if ($element = $this->container->page->find($link)) {
                    $this->container->logHelper->action($this->title . ': Clicked on link ' . $link);
                    $element->click($link);
                    $this->container->reloadPage($this->title);
                    $this->rateDiscussionPosts();
                    if ($element = $this->container->page->find("//div[@id='page']/div/div[@class='breadcrumb']/ul/li[5]/a")) {
                        $element->click("//div[@id='page']/div/div[@class='breadcrumb']/ul/li[5]/a");
                        $this->container->reloadPage($this->title);
                    }
                }
            }
            /// There is more than one page of forum data
            if ($element = $this->container->page->find("link=Next")) {
                $this->container->logHelper->action($this->title . ': Clicked on Next page');
                $element->click("link=Next");
                $this->container->reloadPage($this->title);
                $next++;
            } else {
                $next = false;
            }
        }
    }

    /**
     * This function is used to rate all posts in a discussion.
     * @todo   make this work with Mink
     * @access public
     */
    public function rateAll() {
        /// We need to make sure that the view of the forum is consistant.
        if ($this->selenium->getSelectedLabel("mode_jump") != "Display replies flat, with newest first") {
            $this->selenium->select("mode_jump", "label=Display replies flat, with newest first");
            $this->container->reloadPage($this->title);
        }

        $tables = $this->selenium->getXpathCount("//table");

        for ($tableid = 1; $tableid < $tables; $tableid++) {
            $basexpath = "//table[$tableid]/tbody/tr[2]/td[2]/div[3]";

            if ($element = $this->container->page->find("$basexpath/select")) {
                $options = $this->selenium->getSelectOptions("$basexpath/select");
                $label   = "label=" . $options[rand(1, count($options) - 1)];
                $this->selenium->select("$basexpath/select", $label);
            }
        }

        /// Skip the forum if there is no button to rate the posts. probably means ajax ratings
        if ($element = $this->container->page->find("//input[@value='Send in my latest ratings']")) {
            $element->click("//input[@value='Send in my latest ratings']");
            $this->container->reloadPage($this->title);
            $element->click("link=Continue");
            $this->container->reloadPage($this->title);
        }
    }

    /**
     * This function is used to bookmark or unbookmark a discussion
     */
    public function bookmark() {
        if ($element = $this->container->page->findLink($this->flagBookmarkUrl)) {
            $this->container->logHelper->action($this->title . ': Bookmarked ' . $this->title);
            $element->click();
        }
    }

    /**
     * This function is used to mark or unmark a discussion as substantative
     */
    public function substantive() {
        if ($element = $this->container->page->findLink($this->flagSubstantiveUrl)) {
            $this->container->logHelper->action($this->title . ': Marked as substantitive ' . $this->title);
            $element->click();
        }
    }

    /**
     * This function is used to click on the author's profile.
     */
    public function authorProfile() {
        if ($element = $this->container->page->findLink($this->authorUrl)) {
            $this->container->logHelper->action($this->title . ': Visiting ' . $this->author . '\'s profile page');
            $element->click();
            $this->container->reloadPage($this->title);
        }
    }

    public function getTitle() {
        return $this->title;
    }
}