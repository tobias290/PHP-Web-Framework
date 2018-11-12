<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 12/06/2017
 * Time: 16:20
 */

namespace Framework\Console\Commands;

use Framework\Database\DB;
use Framework\Exceptions\KeyNotFoundException;
use Framework\Kernel;

/**
 * Class Command
    * Base class to creating a command
 * @package Framework\Console\Commands
 */
abstract class Command {
    /**
     * @var Kernel
     *
     * Instance of the kernel.
     */
    protected $kernel;

    /**
     * @var array - List of arguments when executing this command
     */
    private $args;

    /**
     * @var array - List of flags that were added to this command
     * NOTE: if a flag was directly executed then the command was not ran
     */
    private $flags;

    /**
     * @var string - Sets the name of the command e.g. add:user
     *
     * Can also have arguments e.g add:user {name}
     * Can also have optional arguments e.g add:user {name?}
     * Can also have default arguments e.g add:user {name=Toby}
     */
    protected static $name;

    /**
     * @var string - Sets a brief description of the command which is outputted when the user runs the command 'php app list'
     */
    protected static $description = null;

    /**
     * @var string - Sets more detailed description is the user needs help on a command e.g add:user --help
     */
    protected static $help = null;

    /**
     * Command constructor.
     *
     * @param Kernel $kernel - Kernel instance.
     */
    public function __construct($kernel) {
        $this->kernel = $kernel;
    }

    /**
     * Returns the name of the command
     */
    final public static function getName() {
        return static::$name;
    }

    /**
     * Returns the Commands description
     */
    final public static function getDescription() {
        return static::$description;
    }

    /**
     * Returns the Commands help description
     */
    final public static function getHelp() {
        return static::$help;
    }

    /**
     * Sets the Commands arguments
     * @param array ...$args - Arguments to be set
     */
    final public function setArgs($args) {
        $this->args = $args;
    }

    /**
     * Returns the Commands arguments
     * @return array
     */
    final protected function getArgs() {
        return $this->args;
    }

    /**
     * Return a certain key in the argument list
     * @param string $key - Key to look for
     * @param boolean $crash - Decides whether the program should raise an error if the key doesn't exist
     * @return mixed
     * @throws KeyNotFoundException | null - Thrown if key does not exist
     */
    final protected function getArg($key, $crash=true) {
        if(key_exists($key, $this->args))
            return $this->args[$key];
        else if(!$crash)
            return null;
        else
            throw new KeyNotFoundException("'$key' was not found in argument list");
    }

    /**
     * Sets the Commands flags
     * @param array ...$flags - Flags to be set
     */
    final public function setFlags($flags) {
        $this->flags = $flags;
    }

    /**
     * Returns the Commands flags
     * @return array
     */
    final protected function getFlags() {
        return $this->flags;
    }

    /**
     * @param string $key - Flag to look for
     * @return bool - Returns whether flag exists
     */
    final protected function hasFlag($key) {
        return in_array($key, $this->flags);
    }

    /**
     * Sets up the Database and config instance before any command is executed
     */
    final public function init() {
        if(!DB::isConnected())
            $this->setUpDatabase();
    }

    /**
     * Sets up the Database
     */
    private function setUpDatabase() {
        DB::connect();

        foreach (DB::tables() as $table) {
            DB::addTableInstance($table);
        }
    }

    /**
     * Method called when the command is executed
     */
    abstract public function execute();
}