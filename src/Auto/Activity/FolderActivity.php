<?php
/**
 * Folder activity class
 * @package    Activity
 * @subpackage Folder
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Activity;

use Auto\Activity\Activity;

/**
 * Folder activity class. Extends the activity and does everything by default.
 */
class FolderActivity extends Activity {

    /**
     * @var string The activity type.
     */
    protected $type = 'folder';

    /**
     * Add a file to the folder resource in the creation screen
     */
    public function fillOutRequiredFields() {
        $this->container->contentHelper->uploadRandFile($this->container->cfg->filedir, 'math', 'pdf');
    }
}