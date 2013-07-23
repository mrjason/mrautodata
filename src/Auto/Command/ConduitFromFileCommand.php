<?php

/**
 * Conduit command file
 * @package    Command
 * @subpackage ConduitFromFile
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
 * ConduitFromFileCommand converts a conduit file to a webservices request.
 */
class ConduitFromFileCommand extends Command {
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'Specific site to be used', 'all')
            ->addOption('file', 'f', InputOption::VALUE_OPTIONAL, 'Specific site to be used', 'upload.csv')
            ->setName('conduitfromfile')
            ->setAliases(array('cff'))
            ->setDescription('Command executes a series of Moodle actions for a number of users based on the day of the week.  This is intended to be run nightly')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command executes a series of Moodle actions as a salesnightly:

  <info>%application.name% %command.name%</info>

You can request actions be taken on specific courses using the <comment>--coursestart</comment> option:

  <info>%application.name% %command.name% --course 10</info>
    
You can also request actions be taken by specific user ids using the <comment>--userstart</comment> option:

  <info>%application.name% %command.name% --users 10
  
You can also request actions be taken by a specific set of usernames using the <comment>--username</comment> option:

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
        $sitesused = $input->getOption('site');

        $site = $this->getHelper('site')->getSiteAsObject($sitesused);

        $file    = $input->getOption('file');
        $conduit = $this->getHelper('conduit');
        $row     = 1;

        $conduit->setUrl($site->url);
        if (($handle = fopen($file, "r")) !== false) {

            /// grab first row of headers
            if (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $num = count($data);
                $row++;
                $headers = array();
                for ($c = 0; $c < $num; $c++) {
                    $headers[$data[$c]] = $c;
                }
            }

            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $row++;
                $user = array(
                    'username'    => $data[$headers['username']],
                    'password'    => $data[$headers['password']],
                    'idnumber'    => rand(0, 1000000),
                    'firstname'   => $data[$headers['firstname']],
                    'lastname'    => $data[$headers['lastname']],
                    'institution' => $data[$headers['institution']],
                    'email'       => $data[$headers['email']],
                    'city'        => $data[$headers['city']],
                    'country'     => $data[$headers['country']],
                    'htmleditor'  => '0'
                );
                $conduit->user($user, 'update');
                for ($i = 1; $i < 5; $i++) {
                    $course = 'course' . $i;
                    $roleid = 'role' . $i;
                    if (!empty($data[$headers[$course]])) {
                        switch ($data[$headers[$roleid]]) {
                            case 1:
                                $role = 'student';
                                break;
                            case 2:
                                $role = 'editingteacher';
                                break;
                            case 3:
                                $role = 'teacher';
                                break;
                            default:
                                $role = $data[$headers[$roleid]];
                        }
                        $start = \DateTime::createFromFormat('m-d-Y:H', '7-12-2011:01')->getTimestamp();
                        $end   = \DateTime::createFromFormat('m-d-Y:H', '7-15-2011:15')->getTimestamp();
                        $conduit->enroll($data[$headers['username']], $data[$headers[$course]], $role, $start, $end);
                    }
                }

            }
            fclose($handle);
        }

    }

}
    