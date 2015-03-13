<?php

/**
 * List sites command file
 * @package    Command
 * @subpackage ListSites
 * @author     Jason Hardin <jason@moodlerooms.com>
 * @copyright  Copyright (c) 2012, Moodlerooms Inc
 */

namespace Auto\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ListSitesCommand lists the sites in the site configuration file.
 */
class ListSitesCommand extends Command {
    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this
            ->addOption('site', 's', InputOption::VALUE_OPTIONAL, 'Specific site alias to list the url for', 'all')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Specific group of sites to list', 'nightly')
            ->addOption('alias', 'a', InputOption::VALUE_NONE, 'Return only the alias')
            ->addOption('url', 'u', InputOption::VALUE_NONE, 'Return only the urls')
            ->setName('listsites')
            ->setAliases(array('ls'))
            ->setDescription('Command executes a series of moodle actions for a number of users based on the day of the week.  This is intended to be run nightly')
            ->setHelp(<<<EOF
The <info>%command.name%</info> lists out all of the sites that commands can be executed on based on type.:

  <info>%application.name% %command.name%</info>

You can request actions be taken on specific courses using the <comment>--coursestart</comment> option:

  <info>%application.name% %command.name% --site mm</info>
 
You can request actions be taken on specific courses using the <comment>--coursestart</comment> option:

  <info>%application.name% %command.name% --type sales</info>
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
        $type      = $input->getOption('type');
        $urlonly   = $input->getOption('url');
        $aliasonly = $input->getOption('alias');

        if ($sitesused == 'all') {
            $sites = $this->getHelper('site')->getSites($type);
        } else {
            $sites = array($this->getHelper('site')->getSiteAsObject($sitesused));
        }

        $list = '';
        foreach ($sites as $alias => $site) {
            if ($urlonly) {
                $list .= $site->url . "\n";
            } else if ($aliasonly) {
                $list .= "$alias \n";
            } else {
                $list .= 'alias: ' . $alias . '   url: ' . $site->url . "\n";
            }
        }
        print $list;
    }
}

?>