<?php

namespace Auto;

/**
 * @package   Auto
 * @author    Jason Hardin <jason@moodlerooms.com>
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 */
class Auto {
    const VERSION = '1.0.0';

    protected $configFile;
    protected $defaultConfigFile;
    protected $emailAdminsConfigFile;
    protected $staticConfigFile;
    protected $sitesConfigFile;
    protected $usersConfigFile;

    public function __construct() {
        $configDir = getenv('AUTOCONFIGDIR');
        if (empty($configDir)) {
            $configDir = getenv('HOME');
        }
        if (empty($configDir) or !is_dir($configDir)) {
            throw new \RuntimeException('Failed to find a directory to store the config file, refer to the README.md');
        }
        $this->setConfigFile($configDir . '/.Autoconfig.yml')
            ->setDefaultConfigFile(__DIR__ . '/Resources/Yaml/ConfigDefault.yml')
            ->setEmailAdminsConfigFile(__DIR__ . '/Resources/Yaml/ConfigEmailAdmins.yml')
            ->setStaticConfigFile(__DIR__ . '/Resources/Yaml/ConfigStatic.yml')
            ->setSitesConfigFile(__DIR__ . '/Resources/Yaml/ConfigSites.yml')
            ->setUsersConfigFile(__DIR__ . '/Resources/Yaml/ConfigUsers.yml');
    }

    /**
     * @param $configFile
     *
     * @return Auto
     */
    public function setConfigFile($configFile) {
        $this->configFile = $configFile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getConfigFile() {
        return $this->configFile;
    }

    /**
     * @param $emailAdminsConfigFile
     *
     * @return Auto
     */
    public function setEmailAdminsConfigFile($emailAdminsConfigFile) {
        $this->emailAdminsConfigFile = $emailAdminsConfigFile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmailAdminsConfigFile() {
        return $this->emailAdminsConfigFile;
    }

    /**
     * @param $defaultConfigFile
     *
     * @return Auto
     */
    public function setDefaultConfigFile($defaultConfigFile) {
        $this->defaultConfigFile = $defaultConfigFile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultConfigFile() {
        return $this->defaultConfigFile;
    }

    /**
     * @param $staticConfigFile
     *
     * @return Auto
     */
    public function setStaticConfigFile($staticConfigFile) {
        $this->staticConfigFile = $staticConfigFile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStaticConfigFile() {
        return $this->staticConfigFile;
    }

    /**
     * @param $sitesConfigFile
     *
     * @return Auto
     */
    public function setSitesConfigFile($sitesConfigFile) {
        $this->sitesConfigFile = $sitesConfigFile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSitesConfigFile() {
        return $this->sitesConfigFile;
    }

    /**
     * @param $usersConfigFile
     *
     * @return Auto
     */
    public function setUsersConfigFile($usersConfigFile) {
        $this->usersConfigFile = $usersConfigFile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsersConfigFile() {
        return $this->usersConfigFile;
    }
}