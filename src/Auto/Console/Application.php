<?php

namespace Auto\Console;

use Auto\Auto;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Finder\Finder;
use \Symfony\Component\Yaml\Yaml;

class Application extends BaseApplication {
    /**
     * @var \Auto\Auto
     */
    protected $Auto;

    public function __construct() {
        parent::__construct('Auto', Auto::VERSION);
        $this->setAuto(new Auto());

        /** @var $helperSet \Symfony\Component\Console\Helper\HelperSet */
        $helperSet = $this->getHelperSet();

        $finder = new Finder();
        $finder->files()->name('*.php')->in(dirname(__DIR__) . '/Helper');

        /** @var $file \Symfony\Component\Finder\SplFileInfo */
        foreach ($finder as $file) {
            $class = 'Auto\Helper\\' . $file->getBasename('.php');
            $helperSet->set(new $class());
        }

        // Set configuration
        if (file_exists($this->getAuto()->getConfigFile())) {
            /** @var $config \Auto\Helper\ConfigHelper */
            $config = $helperSet->get('config');
            $config->setConfig(array_merge(
                Yaml::parse($this->getAuto()->getConfigFile()),
                Yaml::parse($this->getAuto()->getEmailAdminsConfigFile()),
                Yaml::parse($this->getAuto()->getSitesConfigFile()),
                Yaml::parse($this->getAuto()->getUsersConfigFile())
            ));
        }

        $finder = new Finder();
        $finder->files()->name('/\w+Command\.php$/')->in(dirname(__DIR__) . '/Command');

        /** @var $file \Symfony\Component\Finder\SplFileInfo */
        foreach ($finder as $file) {
            $class = 'Auto\Command\\' . $file->getBasename('.php');
            $this->add(new $class());
        }

        // Silly, but let's make our help/list commands display proper commands
        $placeholders = array('php app/console');
        $replacements = array(strtolower($this->getName()));

        foreach (array('list', 'help') as $commandName) {
            /** @var $command \Symfony\Component\Console\Command\Command */
            $command = $this->get($commandName);
            $command->setHelp(str_replace($placeholders, $replacements, $command->getHelp()));
        }
    }

    /**
     * @param \Auto\Auto $Auto
     *
     * @return Application
     */
    public function setAuto($Auto) {
        $this->Auto = $Auto;
        return $this;
    }

    /**
     * @return \Auto\Auto
     */
    public function getAuto() {
        return $this->Auto;
    }
}