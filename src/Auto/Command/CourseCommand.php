<?php
/**
 * Course command file
 * @package    Command
 * @subpackage Course
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
 * CourseCommand
 * Use to create, update and delete courses via the user interface.
 * In order for create to work the Admin user must have the html editor set to use standard web form
 */
class CourseCommand extends Command {
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addArgument('action', InputArgument::REQUIRED, 'create, update or delete the course')
            ->addOption('courseinfo', 'container', InputOption::VALUE_OPTIONAL, 'Course shortname/Course fullname', 'ACFV/Automated Course Folderview')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Course format', 'topics')
            ->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'Specific site to be used, this is the alias in the sites file use the ls command to find all sites available', 'all')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Specific batch of sites to be used', 'nightly')
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'Login username perform actions with', 'admin')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Sets the password for the user\'s being created', '')
            ->setName('course')
            ->setAliases(array('cou'))
            ->setDescription('%command.name% is used to create, update and delete courses via the user interface. In order for create to work the Admin user must have the html editor set to use standard web form.')
            ->setHelp(<<<EOF
You can request actions be taken on a specific site using the <comment>--site</comment> option:

  <info>%application.name% %command.name% create --site hed</info>

You can request actions be taken on a specific set of site using the <comment>--type</comment> option (the type must exist in the Sites file use the ls command to find them):

  <info>%application.name% %command.name% --type batch1</info>
  
You can also request the the post fix and number of users created begins at a certain number by using the <comment>--userbegin</comment> option:

  <info>%application.name% %command.name% --userbegin 0
    
You can also request the the post fix and number of users created ends at a certain number minus one by using the <comment>--userend</comment> option:

  <info>%application.name% %command.name% --userend 10
  
You can also request that all users being created are created using a specific username prefix by using the <comment>--username</comment> option:

  <info>%application.name% %command.name% --username user</info>
EOF
            );
    }

    /**
     * {@inheritdoc}
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $action = $input->getArgument('action');
        $course = array();
        list($course['shortname'], $course['fullname']) = explode('/', $input->getOption('courseinfo'));
        $course['format'] = $input->getOption('format');
        $sitesused        = $input->getOption('site');
        $batch            = $input->getOption('type');

        if ($sitesused == 'all') {
            $sites = $this->getHelper('site')->getSites($batch);
        } else {
            $sites = array($this->getHelper('site')->getSiteAsObject($sitesused));
        }
        $password   = $input->getOption('password');
        $useconduit = $cfg->get('conduit', 'enabled');
        if ($useconduit) {
            $conduit = $this->getHelper('conduit');
        }
        $j2 = new Joule2(new Container($this->getHelper('config'), $this->getHelper('content'), $this->getHelper('log')));

        foreach ($sites as $site) {
            $conduit->setUrl($site->url);
            $j2->setSite($site);

            $user = $this->getHelper('user');
            $user->setUsername($input->getOption('username'));
            $user->setPassword($password);
            $users = $user->getUsers();
            $admin = $users[0];

            switch ($action) {
                case 'create':
                    /// Check if course exists

                    $j2->login($admin);
                    /// create course
                    $course = $j2->createCourse($course);
                    $course->createActivities();
                    $j2->logout();
                    break;
                case 'delete':
                    /// Send conduit delete
                    break;
                case 'update':
                    /// TODO: determine what to do here, send a conduit update or add content
                    break;
                case 'backup':
                    /// TODO: Send conduit archive commend or manually backup the course in joule.
                case 'restore':
                    /// TODO: manually restore a course based on a file name
                    break;

            }

            $j2->teardown();
        }
        $j2->cleanup();
    }
}
    