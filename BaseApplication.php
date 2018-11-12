<?php
/**
 * Created by PhpStorm.
 * User: toby
 * Date: 22/02/2018
 * Time: 17:08
 */

namespace Framework;


use Framework\Console\Commands\{Command,
    Database\DropTableCommand,
    ListCommand,
    MakeNewCommand,
    StartServer,
    Auth\CreateGroupCommand,
    Auth\CreatePermissionCommand,
    Auth\CreateUserCommand,
    Database\MigrationCommand,
    Database\ShowTableColumnsCommand,
    Database\ShowTablesCommand,
    Database\SQLQueryCommand,
    Database\TruncateTableCommand};
use Framework\Database\DB;
use Framework\Http\Routing\Router;
use Framework\Security\Auth\Models\Group;
use Framework\Security\Auth\Models\Permission;
use Framework\Security\Auth\Models\User;

/**
 * Class BaseApplication
    * All configuration such as middleware, commands are set-up via this class
 * @package Framework
 */
abstract class BaseApplication {
    private static $instance;

    final public function __construct() {
        self::$instance = $this;
    }

    /**
     * Returns the class instance or creates one if it doesn't exist
     */
    final public static function instance() {
        if(self::$instance === null)
            self::$instance = new static;
        return self::$instance;
    }

    /**
     * Runs the application
     */
    final public function run() {
        Router::globalMiddleware($this->globalMiddleware());
        Router::routeMiddleware($this->routeMiddleware());

        foreach (array_merge($this->builtinTables(), $this->tables()) as $table) {
            DB::add($table);
        }

        foreach (glob("routes/*.php") as $route) {
            require_once $route;
        }

        Router::request();
    }

    /**
     * Runs the application from the command line (This is called when the user executes a command)
     *
     * @param array $argv - Array of arguments passed to script
     * @param int $argc - The number of arguments passed to script
     * @throws Exceptions\CommandNotFound - Thrown if a command doesn't exist
     */
    final public function runConsole($argv, $argc) {
        foreach (array_merge($this->builtinTables(), $this->tables()) as $table) {
            DB::add($table);
        }

        $kernel = new Kernel($this, $argv, $argc);

        /** @var Command $command */
        $command = $kernel->run();

        // If command is null then a flag was called
        if($command != null) {
            $command->init();
            $command->execute();
        }
    }

    /**
     * @return array - Returns any tables created by the framework (e.g. Auth tables)
     */
    final public function builtinTables(): array {
        // Add the authentication classes to the list
        return [
            Permission::class,
            Group::class,
            User::class,
        ];
    }

    /**
     * @return array - Returns a list of any tables created by the user
     */
    public function tables(): array {
        return [];
    }

    /**
     * @return array - Returns a list of all the global middleware
     */
    public function globalMiddleware(): array {
        return [];
    }

    /**
     * @return array - Returns an associate list of all the route middleware
     */
    public function routeMiddleware(): array {
        return [];
    }

    /**
     * @return array - Returns a list of all the build in commands
     */
    final public function builtinCommands(): array {
        return [
            ListCommand::class,
            MakeNewCommand::class,
            StartServer::class,
            MigrationCommand::class,
            ShowTablesCommand::class,
            ShowTableColumnsCommand::class,
            SQLQueryCommand::class,
            DropTableCommand::class,
            TruncateTableCommand::class,
            CreateUserCommand::class,
            CreatePermissionCommand::class,
            CreateGroupCommand::class,
        ];
    }

    /**
     * @return array - Returns a list of any command created by the user
     */
    public function commands(): array {
        return [];
    }
}