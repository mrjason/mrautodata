<?php

/**
 * Nightly data generation for sales command file
 * @package    Command
 * @subpackage Nightly
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
 * NightlyCommand executes the nightly sales processes to add data base don user interactions.
 */
class NightlyCommand extends Command {
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'Specific site to be used, use an alias', 'all')
            ->addOption('batch', 'b', InputOption::VALUE_OPTIONAL, 'Specific batch of sites to be used', 'nightly')
            ->addOption('totalusers', 't', InputOption::VALUE_OPTIONAL, 'Total number of users to be selected from.  Must be a multiple of 7', 7)
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'Login username perform actions with', 'student')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Sets the password for the user\'s being created')
            ->setName('nightly')
            ->setAliases(array('n'))
            ->setDescription('Command executes a series of Moodle actions for sites and a number of users based on the day of the week.  This is intended to be run nightly to generate report data.')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command executes a series of Moodle actions as a datageneration:

  <info>%application.name% %command.name%</info>

You can request actions be taken on a specific site using the <comment>--site</comment> option:

  <info>%application.name% %command.name% --site hed </info>
    
You can also request actions be taken by a total number of users divided by 7 (users per day) using the <comment>--totalusers</comment> option:

  <info>%application.name% %command.name% --totalusers 7
  
You can also request actions be taken by a specific username using the <comment>--username</comment> option:

  <info>%application.name% %command.name% site --username user</info>
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
        $batch     = $input->getOption('batch');

        if ($sitesused == 'all') {
            $sites = $this->getHelper('site')->getSites($batch);
        } else {
            $sites = array($this->getHelper('site')->getSiteAsObject($sitesused));
        }

        $log        = $this->getHelper('log');
        $cfg        = $this->getHelper('config');
        $j2         = new Joule2(new Container($cfg, $this->getHelper('content'), $log));
        $useconduit = $cfg->get('conduit', 'enabled');
        $username = $input->getOption('username');
        if (!$password = $input->getOption('password')) {
            $password = $cfg->get('users', $username);
        }

        foreach ($sites as $site) {
            try {
                $j2->setSite($site);
                $userHelper = $this->getHelper('user');

                $numusers  = $input->getOption('totalusers') / 7;
                $dayofweek = date('w') + 1;
                $startuser = $numusers * $dayofweek;
                $enduser   = $numusers * $dayofweek + $numusers;
                $userIds   = array($startuser, $enduser);
                $userHelper->setUserIds($userIds);
                $userHelper->setUsername($username);
                $userHelper->setPassword($password);

                $users = $user->getUsers();

                foreach ($users as $user) {
                    if ($useconduit) {
                        //set html editor
                    }
                    if ($j2->login($user)) {
                        $courses = $j2->getCourses(); // Might want to cache this for all users if we can assume they are enrolled in the same courses.

                        foreach ($courses as $course) {
                            $course->view();
                            $activities = $course->getActivities();
                            foreach ($activities as $activity) {
                                // $interact = !rand(0, 4);
                                //  if ($interact) {
                                $grade = rand(60, 100);
                                $j2->interactWithActivity($activity, $grade);
                                $course->view();
                                //  } else {
                                //     $log->action($course->getFullname() . ': Skipped activity ' . $activity->getTitle());
                                // }
                            }
                        }
                        $j2->logout();
                    }
                    if ($useconduit) {
                        //reset html editor
                    }
                }
                $j2->teardown();
            } catch (Exception $e) {
                print $e;
            }
        }
        $j2->cleanup();
    }
}
    
