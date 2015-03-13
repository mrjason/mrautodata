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
class UserCommand extends Command {
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'Specific site to be used (should be a alias)', 'all')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Specific batch of sites to be used', 'sales')
            ->addOption('userbegin', 'b', InputOption::VALUE_OPTIONAL, 'User id start to perform actions with', 0)
            ->addOption('userend', 'e', InputOption::VALUE_OPTIONAL, 'User id end to perform actions with', 10)
            ->addOption('username', 'u', InputOption::VALUE_OPTIONAL, 'Login username perform actions with', 'user')
            ->addOption('password', 'p', InputOption::VALUE_OPTIONAL, 'Sets the password for the user\'s being created', '')
            ->addOption('language', 'l', InputOption::VALUE_OPTIONAL, 'Sets the for any information entered by the user', 'EN')
            ->addOption('random', 'r', InputOption::VALUE_OPTIONAL, 'Randomly determines the user\'s first and last name
            recommended to use without a picture', '')
            ->addOption('nopicture', 'np', InputOption::VALUE_NONE, 'Doesn\'t upload a picture of the user')
            ->setName('user')
            ->setAliases(array('u'))
            ->setDescription('%command.name% executes a series of Conduit RestFul web service requests to setup a site to work with the sales nightly command. The script also logs in as the user and upload a user profile picture.')
            ->setHelp(<<<EOF
The <info>%command.name%</info> executes a a series of Conduit RestFul web service requests to setup a site to work with the sales nightly command use:

  <info>.%application.name% %command.name%</info>
  
You can request actions be taken on a specific site using the <comment>--site</comment> option:

  <info>.%application.name% %command.name% --site hed</info>

You can request actions be taken on a specific set of site using the <comment>--type</comment> option (the type must exist in the Sites file use the ls command to find them):

  <info>.%application.name% %command.name% --type batch1</info>

You can request the the post fix and number of users created begins at a certain number by using the <comment>--userbegin</comment> option:

  <info>.%application.name% %command.name% --userbegin 0</info>
    
You can request the the post fix and number of users created ends at a certain number minus one by using the <comment>--userend</comment> option:

  <info>.%application.name% %command.name% --userend 10</info>
  
You can request that all users being created are created using a specific username prefix by using the <comment>--username</comment> option:

  <info>.%application.name% %command.name% --username user</info>

You can request that the content posted by the user be made in a specific language by using the <comment>--language</comment>
option:

  <info>.%application.name% %command.name% --language EN</info>

Languages currently supported are EN = English, ES = Spanish
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

        $conduit    = $this->getHelper('conduit');
        $begin      = $input->getOption('userbegin');
        $end        = $input->getOption('userend');
        $lang       = $input->getOption('language');
        $randomName = $input->getOption('random');
        $logHelper  = $this->getHelper('log');
        $cfgHelper  = $this->getHelper('config');

        $cfgHelper->setLanguage($lang);
        $contentHelper = $this->getHelper('content');
        $j2            = new Joule2(new Container($cfgHelper, $contentHelper, $logHelper));
        $username      = $input->getOption('username');

        if (!$password = $input->getOption('password')) {
            $password = $cfgHelper->get('users', $username);
        }

        $conduit->setToken($cfgHelper->get('conduit', 'token'));

        foreach ($sites as $site) {
            print('Setting up site ' . $site->url . "\n");

            $conduit->setUrl($site->url);
            $userHelper = $this->getHelper('user');
            $userHelper->setUsername($username);
            $userHelper->setPassword($password);
            $userHelper->loadUsers($cfgHelper->getLanguage());
            if ($begin != $end) {
                $userHelper->setUserIds(array($begin, $end));
            }

            if($randomName) {
                $users = $userHelper->getRandomNamedUsers();
            } else {
                $users = $userHelper->getUsers();
            }
            $j2->setSite($site);

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
                $conduit->user($conduituser, 'update');
                if (!$input->getOption('nopicture')) {
                    if ($j2->login($user)) {
                        $j2->addProfilePicture('userpix/' . $user->username . '.jpg');
                        $j2->logout();
                    }
                }
            }

            $j2->teardown();
            $j2->cleanup();
        }
    }
}
    
