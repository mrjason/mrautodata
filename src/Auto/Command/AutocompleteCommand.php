<?php
/**
 * @see https://github.com/KnpLabs/KnpConsoleAutocompleteBundle/blob/master/Command/AutocompleteCommand.php
 */

namespace Auto\Command;

use Auto\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Initializes a new application.
 * @package    symfony
 * @subpackage console
 * @author     Matthieu Bontemps <matthieu@knplabs.com>
 */
class AutocompleteCommand extends Command {
    /**
     * @see Command
     */
    protected function configure() {
        $this
            ->setDefinition(array(
                                 new InputArgument('command_name', InputArgument::OPTIONAL, 'A command name to generate Autocomplete options for'),
                            ))
            ->setName('Autocomplete')
            ->setDescription('Helps with Autocompletion')
            ->setHelp(<<<EOT
The <info>Autocomplete</info> will provide Autocompletion facilities for shells.
For the moment, it just conveniently lists all commands in a shell friendly format.
EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $commandName = $input->getArgument('command_name');

        if ($commandName !== null && $this->getApplication()->has($commandName)) {
            $this->AutocompleteOptionName($input, $output, $commandName);

        } else {
            $this->AutocompleteCommandName($input, $output);
        }
    }

    protected function AutocompleteOptionName(InputInterface $input, OutputInterface $output, $commandName) {
        $options = array_merge(
            $this->getApplication()->get($commandName)->getDefinition()->getOptions(),
            $this->getApplication()->getDefinition()->getOptions()
        );
        $options = array_map(function ($option) {
            return '--' . $option->getName();
        }, $options);
        $output->write(join(" ", $options), false);
    }

    protected function AutocompleteCommandName(InputInterface $input, OutputInterface $output) {
        $commands = $this->getApplication()->all();
        $commands = array_keys($commands);
        $output->write(join(" ", $commands), false);
    }
}