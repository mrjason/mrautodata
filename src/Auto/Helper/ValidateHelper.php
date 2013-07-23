<?php
/**
 * ValidateHelper class
 * @package    Helper
 * @subpackage Validate
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 * @author     Mark Nielsen <mark@moodlerooms.com>
 */
namespace Auto\Helper;

use \Symfony\Component\Console\Helper\Helper;

/**
 * Validates the configuration values.
 * @todo make this actually work
 */
class ValidateHelper extends Helper {
    /**
     * Returns the canonical name of this helper.
     * @return string The canonical name
     * @api
     */
    function getName() {
        return 'validate';
    }

    /**
     * Validate teh path provided is a directory
     *
     * @param $path
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function directory($path) {
        $dir = realpath($path);
        if ($dir === false) {
            throw new \InvalidArgumentException(sprintf('Failed to run realpath(\'%s\')', $path));
        }
        if (is_file($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory path is a file path: %s', $dir));
        }
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory path is not a directory: %s', $dir));
        }
        return $path;
    }

    /**
     * Make sure the value is not empty
     *
     * @param $value
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function notEmpty($value) {
        if (empty($value) and $value !== '0' and $value !== 0) {
            throw new \InvalidArgumentException('The value given cannot be empty.');
        }
        return $value;
    }

    /**
     * Validate the name passed has valid characters for a database.
     *
     * @param $name
     *
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function databaseName($name) {
        $dbpattern = '/[^a-zA-Z0-9_-]/i';
        if (preg_replace($dbpattern, '', $name) != $name) {
            throw new \InvalidArgumentException(sprintf('Database name "%s" may contain invalid characters.  Valid characters: %s', $name, trim($dbpattern, '/i')));
        }
        return $name;
    }
}