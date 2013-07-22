<?php
/**
 * DialogHelper Class
 *
 * @package Helper
 * @subpackage DialogHelper
 * @copyright Copyright (c) 2012, Moodlerooms Inc
 * @author Mark Nielsen <mark@moodlerooms.com>
 */

namespace Auto\Helper;

use \Symfony\Component\Console\Helper\DialogHelper as BaseDialogHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Gets config data from the config file
 */
class DialogHelper extends BaseDialogHelper {
    /**
     * @param $question
     * @param $default
     * @param string $sep
     * @return string
     */
    public function getQuestion($question, $default, $sep = ':')
    {
        return $default ? sprintf('<info>%s</info> [<comment>%s</comment>]%s ', $question, $default, $sep) : sprintf('<info>%s</info>%s ', $question, $sep);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param $text
     * @param string $style
     */
    public function writeSection(OutputInterface $output, $text, $style = 'bg=blue;fg=white')
    {
        /** @var $formatter \Symfony\Component\Console\Helper\FormatterHelper */
        $formatter = $this->getHelperSet()->get('formatter');
        $output->writeln(array(
            '',
            $formatter->formatBlock($text, $style, true),
            '',
        ));
    }
}