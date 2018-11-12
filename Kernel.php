<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 06/06/2017
 * Time: 10:28
 */

namespace Framework;

require_once "vendor/autoload.php";

use App\Application;
use Framework\Console\Commands\Command;
use Framework\Console\Flags;
use Framework\Exceptions\CommandNotFound;

// TODO: add doc comments

/**
 * Class Kernel - Singleton
 * @package Framework
 */
final class Kernel {
    /**
     * @var BaseApplication
     *
     * Instance of the application.
     */
    private $application;

    /**
     * @var array - List of arguments
     */
    private $arguments;

    /**
     * @var int - Number of arguments
     */
    private $argument_count;

    /**
     * Kernel constructor.
     *
     * @param BaseApplication $application - Main application instance.
     * @param array $arguments - List of arguments passed from the command line.
     * @param int $argument_count - Number of arguments passed.
     */
    public function __construct($application, $arguments, $argument_count) {
        $this->application = $application;
        $this->arguments = $arguments;
        $this->argument_count = $argument_count;
    }

    /**
     * @return BaseApplication - Returns the main application instance.
     */
    public function getApplication() {
        return $this->application;
    }

    /**
     * Determines what command was executed and if found, return it
     * @return Command - Returns the command when found
     * @throws CommandNotFound - Thrown if the users input does not match any command
     */
    public function run() {
        // List of flags if a flag wasn't immediately executed
        $flags = [];

        /** @var Command $command */
        foreach (array_merge(Application::instance()->builtinCommands(), Application::instance()->commands()) as $command) {
            $command_name = $command::getName();

            // Checks the command against the user's input and continues to the next command if isn't a match
            if($this->arguments[1] != explode(" ", $command_name)[0]) continue;

            // Checks whether a flag was called
            // If a flag was called then it returns without executing the actual command
            // If not found it adds the flags to the $args
            if($this->flagCalled($command, $flags)) {
                return null;
            }

            // Removes the flags from the arguments so that a match can be found
            $this->removeFlags();

            // Array with whether the user's input met the command's requirements
            $result = $this->nameIsMatch($command_name);

            // If it is a match instantiate the command, set the arguments and return the instance
            if($result["is_match"]) {
                /** @var Command $cmd */
                $cmd = new $command($this);
                $cmd->setArgs($result["args"]);
                $cmd->setFlags($flags);

                return $cmd;
            }
        }

        // Thrown if no command matched
        throw new CommandNotFound("Command with these arguments does not exist");
    }

    /**
     * Checks whether a flag was called on this command
     * @param Command $command - The class being checked
     * @param array $flags - Reference to the arguments to be passed to the command
     * @return bool - Returns whether a flag was found and executed
     */
    private function flagCalled($command, &$flags) {
        // For a flag to of been called the argument count would greater than 3
        // E.g. app add:user --flag
        if($this->argument_count < 3) return false;

        $flags_called = false;

        // A count var is needed as sometimes an argument is skipped
        $count = 0;

        foreach ($this->arguments as $argument) {
            // Checks whether the command started with --
            if (
                !preg_match("/^--(?P<name>[a-zA-Z_]*)$/", $argument, $matches) and
                !preg_match("/^--(?P<name>[a-zA-Z_]*)=(?P<value>[a-zA-z_0-9]|@|.*)$/", $argument, $matches)
            ) {
                continue;
            }

            $flag_name = $matches["name"];
            $flag_value = $matches["value"] ?? null;

            if (Flags::isFlag($flag_name)) {
                // Executes the flag
                Flags::{$flag_name}($command);
                $flags_called = true;
                echo "\n";
            } else {
                // If it isn't a pre-set flag add it to the flags list
                //$flags[$count] = $flag_name;

                if (is_null($flag_value)) {
                    $flags[$count] = $flag_name;
                } else {
                    $flags[$flag_name] = $flag_value;
                }
            }
            $count++;
        }
        return $flags_called;
    }

    /**
     * Removes an flag is it matches an argument
     */
    private function removeFlags() {
        foreach ($this->arguments as $i => $argument) {
            // Skips the command name
            if ($i == 0) continue;

            if(preg_match("/^--[a-zA-Z_]*$/", $argument)) {
                unset($this->arguments[$i]);
            }
        }
    }

    /**
     * Determines whether the users input matches a command
     * @param string $name - This is the users input
     * @return array - Returns an array with whether is was a match and the arguments
     */
    private function nameIsMatch($name) {
        // A list that will contain all the arguments to a command if it is a match
        $matches = [];
        // A list contains all the matches and no matches found
        $found_any_matches = [];
        // Removes the 'app' part
        $inputted_name = array_splice($this->arguments, 1);

        $this->removeFlagsFromInputtedName($inputted_name);

        if(strpos($name, "{") and strpos($name, "}")) {
            // $matches is passed so any default Values can be inserted
            $regex = $this->createNameRegex($name, $matches);
        } else {
            // Command has no arguments
            return ["is_match" => $name == $inputted_name[0], "args" => null];
        }

        // Splits the regex to evaluate each part at a time
        $split_regex = explode(" ", $regex);

        // Loops over each regex and evaluate the input at the same index
        foreach ($split_regex as $i => $regex) {
            // If there is no index at position $i continue to next iteration
            if(!key_exists($i, $inputted_name)) $argument = " ";
            else $argument = $inputted_name[$i];

            // Check for a match
            if(preg_match("/$regex/", $argument, $ms)) {
                array_push($found_any_matches, true);
                // $wanted only contains the associative key
                $wanted = $this->getStringKeyOnly($ms);

                // Only push keys if wanted is not empty
                if(!empty($wanted)) foreach ($wanted as $key => $value) {
                    // Only set the value if it isn't empty
                    if($value != "" and $value != " ") $matches[$key] = $value;
                }
            } else {
                array_push($found_any_matches, false);
            }
        }

        // If a single match was false then the command was wrong
        if(!empty($matches) or !in_array(false, $found_any_matches))
            return ["is_match" => true, "args" => $matches];
        else
            return ["is_match" => false, "args" => null];
    }

    /**
     * This loops over the command and removes any flags
     * @param array $inputted_name - The input command split into an array
     */
    private function removeFlagsFromInputtedName(&$inputted_name) {
        foreach ($inputted_name as $i => $name) {
            if(strpos($name, "--") !== false) unset($inputted_name[$i]);
        }
    }

    /**
     * Loops over an array and removes all not associative keys
     * @param array $matches - List to loop over
     * @return mixed - Returns the new array
     */
    private function getStringKeyOnly($matches) {
        foreach ($matches as $key => $value) if (is_int($key)) unset($matches[$key]);
        return $matches;
    }

    /**
     * Takes a command's name and creates the regex associated to it to match against the users input
     * @param string $name - The command being tested
     * @param array $matches (pass by reference) - This is the empty array for the matches so any default Values can be inserted
     * @return string - Returns the command's name in regex form
     */
    private function createNameRegex($name, &$matches) {
        $split_name = explode(" ", $name);

        // Gets the command name from the string contains command name and arguments
        $command = $split_name[0];
        // An array which will contain a list of regex
        $args_list = [];

        // Loops over the command missing out the command name
        foreach (array_splice($split_name, 1) as $argument) {
            $argument = preg_replace_callback("/{\s*(.*)\s*}/", function ($m) use (&$matches){
                // Gets the name of the argument
                $var = $m[1];

                if (strpos($var, "?")) {
                    // Allows for optional argument
                    $var = chop($var, "?");
                    return "(?P<$var>.+)|\s*";
                } elseif (strpos($var, "=")) {
                    // Allows for default value
                    /**
                     * @var array $split_var
                     * [0] => argument name
                     * [1] => argument's default value
                     */
                    $split_var = explode("=", $var);
                    // Inserts the default value to the matches array with was passed by value
                    $matches[$split_var[0]] = $split_var[1];
                    return "(?P<$split_var[0]>.+)|\s*";
                } else {
                    return "(?P<$var>[^\s]+)";
                }
            }, $argument);

            array_push($args_list, $argument);
        }

        return "$command " . implode(" ", $args_list);
    }
}
