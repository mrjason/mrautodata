<?php
/**
 * Command command file
 * @package   Command
 * @author    Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Command;

use Symfony\Component\Console\Command\Command as BaseCommand;

/**
 * Setup other commands it is the base class for auto commands
 * @method \Auto\Console\Application getApplication()
 */
class Command extends BaseCommand {
    /**
     * Get the applicaiton name and then process any help documentation for the command replacing the place holders with the correct text
     * @return mixed|string
     */
    public function getProcessedHelp() {
        $applicationName = strtolower($this->getApplication()->getName());

        $placeholders = array(
            '%application.name%',
            'php app/console'
        );
        $replacements = array(
            $applicationName,
            $applicationName,
        );
        return str_replace($placeholders, $replacements, parent::getProcessedHelp());
    }
}