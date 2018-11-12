<?php

/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 14/06/2017
 * Time: 18:25
 */

namespace Framework\Console;

use Framework\Console\Commands\Command;

/**
 * Class Flags
    * Contains all the flags available to the user
    * e.g --help, --arguments
 * @package Framework\Console
 */
final class Flags {
    /**
     * @var array - List of possible flags that can be executed on a command
     * Array = name => function name
     *
     * all flags with be called using '--' e.g. --help
     * 'help' - Returns the help text associated with that command
     * 'arguments' - Returns the name of command so the user can see what arguments are needed
     * 'description' - Returns the description of a command
     */
    private static $flags = [
        "help" => "help",
        "arguments" => "getArguments",
        "args" => "getArguments",
        "description" => "getDescription",
    ];

    /** Stops instantiation of the class */
    private function __construct() {}

    public static function isFlag($flag) {
        return key_exists($flag, self::$flags);
    }

    /**
     * @param string $name - The name of the method to execute
     * @param array $arguments - The first element of the command class
     */
    public static function __callStatic($name, $arguments) {
        // self::{flag}(argument which is the command);
        self::{self::$flags[$name]}($arguments[0]);
    }

    /**
     * Outputs the help info associated with the command
     *
     * @param Command $command - The class the flag was executed on
     */
    private static function help($command) {
        if(!empty($command::getHelp()))
            echo $command::getHelp();
        else
            echo "The '" . explode(" ", $command::getName())[0] . "' command has no help";
    }

    /**
     * Outputs the arguments associated with the class
     *
     * @param Command $command - The class the flag was executed on
     */
    private static function getArguments($command) {
        echo $command::getName();
    }

    /**
     * Outputs the description associated with the class
     *
     * @param Command $command - The class the flag was executed on
     */
    private static function getDescription($command) {
        if(!empty($command::getDescription()))
            echo $command::getDescription();
        else
            echo "The '" . explode(" ", $command::getName())[0] . "' command has no description";
    }
}