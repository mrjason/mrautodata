<?php
/**
 * Site setup for nightly data generation command file
 * @package    Command
 * @subpackage SetupSite
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */

namespace Auto\Command;

use Auto\Container;
use Auto\Joule2;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * SetupSiteCommand enrolls the users in the correct courses for the nightly sales process.
 */
class EnrollCommand extends Command {
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'Specific site to be used (should be a alias)', 'all')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Specific batch of sites to be used', 'sales')
            ->addOption('course', 'c', InputOption::VALUE_OPTIONAL, 'Course shortname(s) to enroll users in and add groups to', 'Math 101,English 101')
            ->addOption('userbegin', 'b', InputOption::VALUE_OPTIONAL, 'User id start to perform actions with', 0)
            ->addOption('userend', 'e', InputOption::VALUE_OPTIONAL, 'User id end to perform actions with', 10)
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'Login username perform actions with', 'user')
            ->addOption('role', 'r', InputOption::VALUE_OPTIONAL, 'The role the user is assigned in a course', 'student')
            ->setName('enroll')
            ->setAliases(array('e'))
            ->setDescription('%command.name% executes a series of Conduit RestFul web service requests to setup a site to work with the sales nightly command. The script also logs in as the user and upload a user profile picture.')
            ->setHelp(<<<EOF
The <info>%command.name%</info> executes a a series of Conduit RestFul web service requests to setup a site to work with the sales nightly command use:

  <info>.%application.name% %command.name%</info>
  
You can request actions be taken on a specific site using the <comment>--site</comment> option:

  <info>.%application.name% %command.name% --site hed</info>

You can request actions be taken on a specific set of site using the <comment>--type</comment> option (the type must exist in the Sites file use the ls command to find them):

  <info>.%application.name% %command.name% --type batch1</info>

You can request the user to be enrolled in a specific course(s) using the <comment>--course</comment> option, each course should be separated by a comma and uses the course shortname:

  <info>.%application.name% %command.name% --course MATH203,ACME New Hire Orientation,pptc1</info>
  
You can also request the the post fix and number of users created begins at a certain number by using the <comment>--userbegin</comment> option:

  <info>.%application.name% %command.name% --userbegin 0</info>
    
You can also request the the post fix and number of users created ends at a certain number minus one by using the <comment>--userend</comment> option:

  <info>.%application.name% %command.name% --userend 10</info>
  
You can also request that all users being created are created using a specific username prefix by using the <comment>--username</comment> option:

  <info>.%application.name% %command.name% --username user</info>

You can request that the user's role be set to something specific (teacher, manager, etc) when enrolling them in the course using the <comment>--role</comment> option:

  <info>.%application.name% %command.name% --role teacher</info>
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

        $conduit     = $this->getHelper('conduit');
        $role        = $input->getOption('role');
        $inputcourse = $input->getOption('course');
        $courses     = explode(',', $inputcourse);
        $groups      = array('Group A', 'Group B', 'Group C');
        $begin       = $input->getOption('userbegin');
        $end         = $input->getOption('userend');
        $log         = $this->getHelper('log');
        $cfg         = $this->getHelper('config');
        $j2          = new Joule2(new Container($cfg, $this->getHelper('content'), $log));
        $username    = $input->getOption('username');

        $conduit->setToken($cfg->get('conduit', 'token'));

        foreach ($sites as $site) {
            print('Setting up site ' . $site->url . "\n");

            $conduit->setUrl($site->url);
            $user = $this->getHelper('user');
            if ($begin != $end) {
                $user->setUserIds(array($begin, $end));
            }
            $user->setUsername($username);
            $user->setPassword($password);
            $user->setRole($role);
            $users = $user->getUsers();
            foreach ($courses as $useless => $course) {
                foreach ($groups as $userless => $group) {
                    $conduit->groups($course, $group);
                }
            }

            $j2->setSite($site);

            foreach ($users as $user) {
                foreach ($courses as $useless => $course) {
                    $conduit->enroll($user->username, $course, $role);
                    if (isset($user->group)) {
                        $conduit->groups_members($course, $user->group, $user->username);
                    }
                }
            }

            $j2->teardown();
            $j2->cleanup();
        }
    }
}