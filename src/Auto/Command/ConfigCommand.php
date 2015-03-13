<?php
/**
 * Config command file
 * @package    Command
 * @subpackage Config
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * ConfigCommand configures the yaml file.
 */
class ConfigCommand extends Command {
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addOption('update', 'u', InputOption::VALUE_NONE, 'Only configures missing configuration settings')
            ->setName('config')
            ->setAliases(array('cfg'))
            ->setDescription('Initializes and updates config for this application')
            ->setHelp(<<<EOF
The <info>%command.name%</info> help you configure this application and also
helps you upgrade your configuration.

Example of initial configuration:

  <info>%application.name% %command.name%</info>

Example of upgrading configuration:

  <info>%application.name% %command.name% -u</info>
EOF
            );
    }

    /**
     * {@inheritdoc}
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var $dialog \Auto\Helper\DialogHelper */
        $dialog = $this->getHelper('dialog');

        /** @var $validate \Auto\Helper\ValidateHelper */
        $validate = $this->getHelper('validate');

        /** @var $command \Auto\Helper\CommandHelper */
        $command = $this->getHelper('command');

        $update        = $input->getOption('update');
        $newConfigRead = false;

        if (!$input->isInteractive()) {
            throw new \InvalidArgumentException('This command only runs in interactive mode');
        }
        $configs = Yaml::parse(
            $this->getApplication()->getAuto()->getDefaultConfigFile()
        );

        $configFile = $this->getApplication()->getAuto()->getConfigFile();
        if (file_exists($configFile)) {
            $currentConfigs = Yaml::parse($configFile);
        } else {
            $currentConfigs = array();
            $update         = false; // Cannot update if it doesn't exist
        }

        $dialog->writeSection($output, 'Welcome to the Configuration Automator!');

        if ($update) {
            $output->writeln(array(
                '',
                '<info>Only configuring missing configurations.</info>',
                '',
            ));
        }

        $help = array(
            'selenium'      => 'This is the configuration for the selenium server.  It should be left as the default.',
            'dirs'          => 'These are the locations of the files that will be processed by the selenium scripts.',
            'files'         => "This is the directory for the files that will be uploaded to joule",
            'download'      => "This si where Firefox downloads all files to and will be cleaned out each run",
            'log'           => 'This is where the log files for each run will be stored.',
            'notifications' => 'These settings determine the amount of information recorded in the logs and if they are emailed.'
        );

        $newConfigs = array();
        foreach ($configs as $section => $configOptions) {
            $dialog->writeSection($output, sprintf('Section: [%s]', $section));
            if (!empty($help[$section])) {
                $output->writeln(array(
                    sprintf('<comment>%s</comment>', $help[$section]),
                    '',
                ));
            }
            $newConfigOptions = array();
            foreach ($configOptions as $configName => $configValue) {
                if (!empty($currentConfigs[$section]) and !empty($currentConfigs[$section][$configName])) {
                    $currentValue = $currentConfigs[$section][$configName];
                } else {
                    $currentValue = null;
                }
                // Always use the current value over the default
                if (!empty($currentValue)) {
                    $configValue = $currentValue;
                }
                if ($update and !empty($currentValue)) {
                    $output->writeln(sprintf('<info>Skipping, already configured, %s: %s</info>', $configName, $currentValue));
                    $newConfigOptions[$configName] = $currentValue;
                } else {
                    $newConfigOptions[$configName] = $dialog->askAndValidate($output, $dialog->getQuestion(sprintf('%s', $configName), $configValue), array(
                        $validate,
                        'notEmpty'
                    ), false, $configValue);
                    $newConfigRead                 = true;
                }
            }
            $newConfigs[$section] = $newConfigOptions;
        }

        $yaml = Yaml::dump($newConfigs);

        $dialog->writeSection($output, 'Writing to configuration file');
        $output->writeln(array(
            'Generated YAML:',
            "<comment>$yaml</comment>",
        ));

        if (!$newConfigRead) {
            $output->writeln('<info>Didn not read any values from you, skipping writing to file.</info>');
            return 0;
        }
        if (file_exists($configFile) and !$dialog->askConfirmation($output, $dialog->getQuestion(sprintf('Overwrite configuration file (%s)', $configFile), 'yes', '?'))) {
            $output->writeln('<error>Writing aborted!</error>');
            return 1;
        }
        if (file_put_contents($configFile, $yaml) === false) {
            $output->writeln(sprintf('<error>Failed to write to: %s</error>', $configFile));
            return 1;
        }
        $output->writeln(sprintf('<comment>Wrote configuration to: %s</comment>', $configFile));

        return 0;
    }
}
