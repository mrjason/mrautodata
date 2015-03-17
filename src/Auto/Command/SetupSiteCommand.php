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
use Symfony\Component\Yaml\Parser;

/**
 * SetupSiteCommand enrolls the users in the correct courses for the nightly sales process.
 */
class SetupSiteCommand extends Command {
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'Specific site to be used (should be a alias)', 'all')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Specific batch of sites to be used', 'sales')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Sets the password for the user\'s being created', '')
            ->addOption('language', 'l', InputOption::VALUE_OPTIONAL, 'Sets the for any information entered by the user', 'EN')
            ->setName('setupsite')
            ->setAliases(array('su'))
            ->setDescription('%command.name% executes a series of Conduit RestFul web service requests to setup a site to work with the sales nightly command. The script also logs in as the user and upload a user profile picture.')
            ->setHelp(<<<EOF
The <info>%command.name%</info> executes a a series of Conduit RestFul web service requests to setup a site to work with the sales nightly command use:

  <info>.%application.name% %command.name%</info>
  
You can request actions be taken on a specific site using the <comment>--site</comment> option:

  <info>.%application.name% %command.name% --site hed</info>

You can request actions be taken on a specific set of site using the <comment>--type</comment> option (the type must exist in the Sites file use the ls command to find them):

  <info>.%application.name% %command.name% --type batch1</info>

You can request that the user's password be set to something specific when adding them to the site by using the <comment>--password</comment> option:

  <info>.%application.name% %command.name% --password demo</info>

You can request that the content posted by the user be made in a specific language by using the <comment>--language</comment>
option:

  <info>.%application.name% %command.name% --language EN</info>

Languages currently supported are EN = English, ES = Spanish, JA = Japanese
EOF
            );
    }

    /**
     * {@inheritdoc}
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $accounts = [
            'student' => 37,
            'teacher' => 12
        ];

        $sitesused = $input->getOption('site');
        $batch     = $input->getOption('type');
        $yaml      = new Parser();

        if ($sitesused == 'all') {
            $sites = $this->getHelper('site')->getSites($batch);
        } else {
            $sites = array($this->getHelper('site')->getSiteAsObject($sitesused));
        }

        $conduit = $this->getHelper('conduit');
        $lang    = $input->getOption('language');

        try {
            $courses = $yaml->parse(file_get_contents(__DIR__ . '/../Resources/Yaml/Lang/' . $lang . '/Courses.yml'));
        } catch (ParseException $e) {
            printf("Unable to parse the YAML string: %s", $e->getMessage());
        }

        try {
            $enrollments = $yaml->parse(file_get_contents(__DIR__ . '/../Resources/Yaml/Lang/' . $lang . '/Enrollments.yml'));
        } catch (ParseException $e) {
            printf("Unable to parse the YAML string: %s", $e->getMessage());
        }

        try {
            $groups = $yaml->parse(file_get_contents(__DIR__ . '/../Resources/Yaml/Lang/' . $lang . '/Groups.yml'));
        } catch (ParseException $e) {
            printf("Unable to parse the YAML string: %s", $e->getMessage());
        }

        $log     = $this->getHelper('log');
        $cfg     = $this->getHelper('config');
        $content = $this->getHelper('content');
        $j2      = new Joule2(new Container($cfg, $content, $log));

        $conduit->setToken($cfg->get('conduit', 'token'));

        foreach ($sites as $site) {
            print('Setting up site ' . $site->url . "\n");
            $j2->setSite($site);
            $conduit->setUrl($site->url);

            foreach ($courses as $id => $course) {
                $courses[$id]['format']           = 'topics';
                $courses[$id]['groupmode']        = 1;
                $courses[$id]['visible']          = 1;
                $courses[$id]['enablecompletion'] = 1;
                $courses[$id]['startdate']        = time();

                foreach ($groups as $id => $group) {
                    $groups[$id]['course'] = $course['shortname'];
                }
            }
            $log->action('Creating courses');
            $conduit->bulkService('course', $courses);
            $log->action('Creating groups');
            $conduit->bulkService('groups', $groups);

            /// Add users to the site, upload picture and enroll them in the courses.
            foreach ($accounts as $username => $value) {
                if (!$password = $input->getOption('password')) {
                    $password = $cfg->get('users', $username);
                }

                $user = $this->getHelper('user');
                $user->loadUsers();
                $user->setUserIds(array(0, $value));
                $user->setUsername($username);
                $user->setPassword($password);
                $users = $user->getUsers();

                foreach ($users as $user) {
                    $conduituser = array(
                        'username'    => $user->username,
                        'password'    => $password,
                        'firstname'   => $user->firstname,
                        'lastname'    => $user->lastname,
                        'idnumber'    => $user->id,
                        'email'       => $user->email,
                        'city'        => 'Baltimore, MD',
                        'country'     => 'US',
                        'trackforums' => '1'
                    );

                    $log->action('Creating user ' . $user->username . ' ' . $user->firstname . ' ' . $user->lastname);
                    $conduit->user($conduituser);

                    if ($j2->login($user)) {
                        $j2->addProfilePicture('userpix/' . $user->username . '.jpg');
                        $j2->logout();
                    }
                }
            }

            $groupMembers = array();
            foreach ($enrollments as $id => $enrollment) {
                $enrollments[$id]['status'] = 0;

                if (isset($enrollment['group'])) {
                    $group = $enrollment['group'];
                    unset($enrollments[$id]['group']);
                }

                if (isset($group)) {
                    $groupMembers[] = array(
                        'group'  => $group,
                        'user'   => $enrollment['user'],
                        'course' => $enrollment['course']
                    );
                }
            }
            $log->action('Creating enrollments');
            $conduit->bulkService('enroll', $enrollments);
            if (count($groupMembers) > 0) {
                $log->action('Creating group members');
                $conduit->bulkService('groups_members', $groupMembers);
            }
            $j2->teardown();
            $j2->cleanup();
        }
    }
}
    
