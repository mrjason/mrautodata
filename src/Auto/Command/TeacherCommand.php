<?php

/**
 * Teacher command file
 * @package    Command
 * @subpackage Teacher
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
 * TeacherCommand which executes processes on the site or batch of sites as a teacher. Used for grading activities
 */
class TeacherCommand extends Command {
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'Specific site to be used, this is the alias int he sites file use the ls command to find all sites available', 'all')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Specific batch of sites to be used', 'nightly')
            ->addOption('userbegin', 'b', InputOption::VALUE_OPTIONAL, 'User id start to perform actions with', 0)
            ->addOption('userend', 'e', InputOption::VALUE_OPTIONAL, 'User id end to perform actions with', 1)
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'Login username perform actions with', 'teacher')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Sets the password for the user\'s being created', '')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Sets this execution into debug mode only using 1 student and 1 course')
            ->setName('teacher')
            ->setAliases(array('t'))
            ->setDescription('Interacts with 50% of all activities in a all courses a teacher is enrolled in for a site. Also attempts to grade the student submissions for interacted with activities.')
            ->setHelp(<<<EOF
You can request actions be taken on a specific site using the <comment>--site</comment> option:

  <info>%application.name% %command.name% --site hed</info>

You can request actions be taken on a specific set of site using the <comment>--type</comment> option (the type must exist in the Sites file use the ls command to find them):

  <info>%application.name% %command.name% --type batch1</info>
  
You can also request the the post fix and number of users created begins at a certain number by using the <comment>--userbegin</comment> option:

  <info>%application.name% %command.name% --userbegin 0
    
You can also request the the post fix and number of users created ends at a certain number minus one by using the <comment>--userend</comment> option:

  <info>%application.name% %command.name% --userend 10
  
You can also request that all users being created are created using a specific username prefix by using the <comment>--username</comment> option:

  <info>%application.name% %command.name% --username user</info>
  
You can also request the program to print out debug informaiton to the console by using the <comment>--debug</comment> option:

  <info>%application.name% %command.name% --debug</info>
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

        $debug      = $input->getOption('debug');
        $log        = $this->getHelper('log');
        $cfg        = $this->getHelper('config');
        $j2         = new Joule2(new Container($cfg, $this->getHelper('content'), $log));
        $useconduit = $cfg->get('conduit', 'enabled');
        if ($useconduit) {
            $conduit = $this->getHelper('conduit');
            $conduit->setToken($cfg->get('conduit', 'token'));
        }
        $username = $input->getOption('username');
        if (!$password = $input->getOption('password')) {
            $password = $cfg->get('users', $username);
        }

        foreach ($sites as $site) {
            if ($useconduit) {
                $conduit->setUrl($site->url);
            }

            $j2->setSite($site);
            $user = $this->getHelper('user');

            $userIds = array($input->getOption('userbegin'), $input->getOption('userend'));

            $user->setUserIds($userIds);
            $user->setUsername($username);
            $user->setRole('teacher');
            $user->setPassword($password);

            $users = $user->getUsers();
            if ($debug) {
                $users = array($users[0]);
            }
            foreach ($users as $user) {
                if ($useconduit) {
                    $fields = array('username' => $user->username, 'htmleditor' => '0');
                    try {
                        $conduit->user($fields, 'update');
                    } catch (Exception $e) {
                        print_r($e);
                        /// hopefully this continues without doing anything.
                    }
                }

                if ($j2->login($user)) {
                    $courses = $j2->getCourses(); // Might want to cache this for all users if we can assume they are enrolled in the same courses.

                    if ($debug && isset($courses[0])) {
                        $courses = array($courses[0]);
                    }
                    foreach ($courses as $course) {
                        $course->view();
                        $activities = $course->getActivities();

                        foreach ($activities as $activity) {
                            $grade = rand(60, 100);
                            if ($activity->getType() == 'assign') {
                                $j2->interactWithActivity($activity, $grade, 'teacher');
                                $course->view();
                            }
                        }
                    }
                    $j2->logout();
                }
                if ($useconduit) {
                    $fields['htmleditor'] = '2';
                    try {
                        $conduit->user($fields, 'update');
                    } catch (Exception $e) {
                        /// hopefully this continues without doing anything.
                    }
                }
            }

            $j2->teardown();
        }
        $j2->cleanup();
    }
}
    