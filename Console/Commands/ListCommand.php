<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 14/06/2017
 * Time: 21:43
 */

namespace Framework\Console\Commands;

use Framework\Kernel;
use Framework\Helpers\StringFunctions;

/**
 * Class ListCommand
    * Lists all commands available to the user
 * @package Framework\Console\Commands
 */
final class ListCommand extends Command {
    use StringFunctions;

    protected static $name = "list";
    protected static $description = "Lists all commands";
    protected static $help = "Lists all the commands and their description";

    public function execute() {
        $commands = array_merge(
            $this->kernel->getApplication()->builtinCommands(),
            $this->kernel->getApplication()->commands()
        );

        $data = [];

        /** @var Command $command */
        foreach ($commands as $command) {
            $data["Command"][] = $command::getName();
            $data["Description"][] = $command::getDescription();
        }

        $this->multiColumnStringTable($data, count($commands));
    }
}