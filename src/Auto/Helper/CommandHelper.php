<?php
/**
 * CommandHelper class
 * @package    Helper
 * @subpackage CommandHelper
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */

namespace Auto\Helper;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Process\Process;

/**
 * Helper class that finds the commands to be executed
 */
class CommandHelper extends Helper {
    /**
     * Returns the canonical name of this helper.
     * @return string The canonical name
     * @api
     */
    function getName() {
        return 'command';
    }

    /**
     * Get the absolute path to a command name
     *
     * @param string $command
     *
     * @return null|string
     */
    public function getPath($command) {
        $process = new Process(sprintf('which %s', $command));
        $process->run();

        if ($process->isSuccessful()) {
            $output = trim($process->getOutput());

            if (!empty($output)) {
                return $output;
            }
        }
        return null;
    }
}