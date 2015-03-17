<?php
/**
 * Gets config data from the config file
 * @throws \Exception
 * @author     Mark Nielsen <mark@moodlerooms.com>
 * @package    Helper
 * @subpackage Congif
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */

namespace Auto\Helper;

use Symfony\Component\Console\Helper\Helper;

/**
 * Class ConfigHelper
 * @package    Auto\Helper
 * @author     Mark Nielsen <mark@moodlerooms.com>
 */
class ConfigHelper extends Helper {
    /**
     * Array of configuration options
     * @var array
     */
    protected $config = array();
    protected $lang = "EN";

    /**
     * Returns the canonical name of this helper.
     * @return string The canonical name
     * @api
     */
    public function getName() {
        return 'config';
    }

    /**
     * Set the configuration array
     *
     * @param array $config
     *
     * @return \Kow\Helper\ConfigHelper
     */
    public function setConfig(array $config) {
        $this->config = $config;
        return $this;
    }

    /**
     * Return the config variable
     * @return array
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Get a configuration value
     * @throws \Exception
     *
     * @param string $section The config section name
     * @param string $name    The config name within the section name
     *
     * @return string
     */
    public function get($section, $name) {
        if (!array_key_exists($section, $this->config)) {
            throw new \Exception(sprintf("Section '%s' does not exist in config file", $section));
        }
        if (!array_key_exists($name, $this->config[$section])) {
            throw new \Exception(sprintf("Configuration name '%s' does not exist in section '%s' in config file", $name, $section));
        }
        return $this->config[$section][$name];
    }

    /**
     * Get a section of configs
     *
     * @param string $section The config section name
     *
     * @return mixed
     * @throws \Exception
     */
    public function getSection($section) {
        if (!array_key_exists($section, $this->config)) {
            throw new \Exception(sprintf("Section '%s' does not exist in config file", $section));
        }

        return $this->config[$section];
    }

    public function setLanguage($lang) {
        $this->lang = $lang;
    }

    public function getLanguage() {
        return $this->lang;
    }
}

?>