<?php

/**
 * Create site command file
 * @package    Command
 * @subpackage CreateSite
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
 * CreateSiteCommand creates a site with all the courses and user enrollments.
 */
class CreateSiteCommand extends Command {
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'Specific site to be used (should be a alias)', 'all')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Specific batch of sites to be used', 'sales')
            ->addOption('onlycourses', 'o', InputOption::VALUE_NONE, 'only courses')
            ->addOption('debug', 'd', InputOption::VALUE_NONE, 'Sets this execution into debug mode only using 1 student and 1 course')
            ->setName('createsite')
            ->setAliases(array('crs'))
            ->setDescription('Command executes a a series of Conduit RestFul web service requests to create a site to work with the sales nightly command. The script also logs in as the user and upload a user profile picture. The script expects courses with the shortname MATH203TEMP, MATH203FPTEMP and PAYPALTEMP to exist.')
            ->setHelp(<<<EOF
The <info>%command.name%</info> executes a a series of Conduit RestFul web service requests to setup a site to work with the sales nightly command use:

  <info>%application.name% %command.name%</info>
  
You can request actions be taken on a specific site using the <comment>--site</comment> option:

  <info>%application.name% %command.name% --site hed</info>

You can request actions be taken on a specific set of site using the <comment>--type</comment> option (the type must exist in the Sites file use the ls command to find them):

  <info>%application.name% %command.name% --type batch1</info>
  
You can also request that the users are only enrolled in courses but not created or have their profile image uploaded by using the <comment>--onlycourse</comment> option:

  <info>%application.name% %command.name% --onlycourse</info>
  
You can also request the program to print out debug informaiton to the console by using the <comment>--debug</comment> option:

  <info>%application.name% %command.name% --debug</info>
EOF
            );
    }

    /**
     * {@inheritdoc}
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $sitesused = $input->getOption('site');
        $batch     = $input->getOption('type');

        if ($sitesused == 'all') {
            $sites = $this->getHelper('site')->getSites($batch);
        } else {
            $sites = array($this->getHelper('site')->getSiteAsObject($sitesused));
        }

        $debug       = $input->getOption('debug');
        $onlycourses = $input->getOption('onlycourses');
        $conduit     = $this->getHelper('conduit');
        $randtext    = new ContentHelper();
        $courses     = array();
        if (!$onlycourses) {
            $j2 = new Joule2(new Container($this->getHelper('config'), $this->getHelper('content'), $this->getHelper('log')));
        }
        foreach ($sites as $site) {
            $cfg = $this->getHelper('config')->getConfig($site);
            if (!$onlycourses) {
                $j2->setUp($cfg);
            }

            $conduit->setUrl($site->url);

            $studentHelper = $this->getHelper('user');
            $studentHelper->setUserIds(0, 10);
            $studentHelper->setUsername('student');
            $students = $studentHelper->getUsers();

            $years       = array('2010', '2011', '2012');
            $semesters   = array('Fall', 'Spring', 'Summer');
            $departments = array('English', 'Math', 'Biology', 'Art');
            foreach ($years as $year) {
                foreach ($semesters as $semester) {
                    foreach ($departments as $department) {

                    }
                }
            }

            foreach ($courses as $course) {
                $conduit->createCourse($course);
            }

            foreach ($students as $user) {
                if (!$onlycourses) {
                    $conduituser = array(
                        'username'   => $user->username,
                        'password'   => '',
                        'firstname'  => $randtext->getNameByID($user->id),
                        'lastname'   => $randtext->getNameByID($user->id, 'l'),
                        'idnumber'   => $user->id,
                        'email'      => $user->email,
                        'city'       => 'Baltimore, MD',
                        'country'    => 'US',
                        'htmleditor' => '2'
                    );
                    $conduit->updateUser($conduituser);
                    if ($j2->login($user)) {
                        $j2->addProfilePicture('userpix/' . $user->username . '.jpg');
                        $j2->goToSocial();
                        $j2->logout();
                    }
                }

                foreach ($courses as $useless => $course) {
                    $conduit->enroll($user->username, $course, 'student');
                }
            }

            $teacherHelper = $this->getHelper('user');
            $teacherHelper->setUserIds(array(50, 51));
            $teacherHelper->setUsername('teacher');
            $teacherHelper->setRole('editingteacher');
            $teachers = $teacherHelper->getUsers();

            foreach ($teachers as $user) {
                if (!$onlycourses) {
                    $conduituser = array(
                        'username'   => $user->username,
                        'password'   => '',
                        'firstname'  => $randtext->getNameByID($user->id),
                        'lastname'   => $randtext->getNameByID($user->id, 'l'),
                        'idnumber'   => $user->id,
                        'email'      => $user->email,
                        'city'       => 'Baltimore, MD',
                        'country'    => 'US',
                        'htmleditor' => '2'
                    );
                    $conduit->updateUser($conduituser);
                    if ($j2->login($user)) {
                        $j2->addProfilePicture('userpix/' . $user->username . '.jpg');
                        $j2->goToSocial();
                        $j2->logout();
                    }
                }

                foreach ($courses as $useless => $course) {
                    $conduit->enroll($user->username, $course, 'editingteacher');
                }
            }

            if (!$onlycourses) {
                $j2->teardown();
            }
        }
    }
}
    
