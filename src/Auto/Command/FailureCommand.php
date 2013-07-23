<?php
/**
 * Failure command file
 * @package    Command
 * @subpackage Failure
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */
namespace Auto\Command;

use Auto\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * FailureCommand
 * Use to send an email to the process administrators if there has been a failure on a site.
 */
class FailureCommand extends Command {
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'Specific site to be used, this is the alias in the sites file use the ls command to find all sites available', 'all')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Specific batch of sites to be used', 'nightly')
            ->setName('failure')
            ->setAliases(array('f'))
            ->setDescription('%command.name% is used validate that a site ran correctly and email admins if there was a site with a failure')
            ->setHelp(<<<EOF
You can request actions be taken on a specific site using the <comment>--site</comment> option:

  <info>%application.name% %command.name% create --site hed</info>

You can request actions be taken on a specific set of site using the <comment>--type</comment> option (the type must exist in the Sites file use the ls command to find them):

  <info>%application.name% %command.name% --type batch1</info>
EOF
            );
    }

    /**
     * {@inheritdoc}
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $sitesused = $input->getOption('site');
        $batch     = $input->getOption('type');

        if ($sitesused == 'all') {
            $sites = $this->getHelper('site')->getSites($batch);
        } else {
            $sites = array($this->getHelper('site')->getSiteAsObject($sitesused));
        }

        $log = $this->getHelper('log');
        $log->init();
        $failures = array();
        foreach ($sites as $site) {
            $log->setSite($site);
            if ($log->failed()) {
                $failures[] = $site->url;
            } else {
                $log->delete();
            }
        }

        /// Create the email to send the process admins for the failed sites.
        if (count($failures) > 0) {
            $admins = $this->getHelper('config')->getSection('admins');
            $to     = array();
            foreach ($admins as $admin) {
                $address        = new \stdClass();
                $address->email = $admin->email;
                $address->name  = $admin->name;
                $to[]           = $address;
            }

            $subject = 'Nightly Data Generation Failures for ' . date('l F j, Y');

            $msg = '<p>The following sites have failure logs:</p><ul>';

            foreach ($failures as $f) {
                $msg .= '<li>' . $f . '</il>';
            }
            $msg .= '</ul>';
            $log->send($subject, $msg, $to);
        }
    }
}
    