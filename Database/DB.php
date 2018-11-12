<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 20/05/2017
 * Time: 12:06
 */

namespace Framework\Database;

use Framework\Config;
use Framework\Database\Connections\{
    ConnectionInterface, MySQLConnection, PostgreSQLConnection, ResultTypes
};
use Framework\Exceptions\{
    ClassNotImplemented, ConfigPropertyIncorrect, DatabaseTableError, SectionNotDefined, SQLQueryError, TableNotFound
};
use Framework\Security\Auth\Models\{Group, Permission, User};


/**
 * Class DB
     * Main class for Database connection
     * Creating, retrieving tables tables
 * @package Framework\Database
 */
final class DB {
    /** @var ConnectionInterface */
    private static $connection;

    private static $tables = array();
    private static $tables_instances = array();
    private static $table_names = array();

    private static $reference_tables = [];

    /** Stops instantiation of the class */
    private function __construct() {}

    /**
     * Connects to the Database from users ini file
     *
     * @throws SectionNotDefined - Thrown if the Database section is not defined in the Database
     * @throws ClassNotImplemented - Thrown because as of yet SQLite is not supported by the framework
     * @throws ConfigPropertyIncorrect - Throws in the 'engine' section of the ini file is set incorrectly
     */
    public static function connect() {
        if(self::$connection != null) if(self::$connection->ping()) return;

        if(Config::instance()->database == null)
            throw new SectionNotDefined("Section 'Database' must be defined in 'config.ini'");

        $host = Config::instance()->database->host;
        $user = Config::instance()->database->user;
        $pass = Config::instance()->database->password;
        $db = Config::instance()->database->database;

        switch (Config::instance()->database->engine) {
            case "mysql":
                self::$connection = new MySQLConnection($host, $user, $pass, $db);
                break;
            case "postgresql":
                self::$connection = new PostgreSQLConnection($host, $user, $pass, $db);
                break;
            case "sqlite":
                throw new ClassNotImplemented("sqlite has not been implemented at this present time");
            default:
                throw new ConfigPropertyIncorrect("The 'engine' property is set incorrectly. Must be postgresql, mysql or sqlite");
        }
    }

    /**
     * @return ConnectionInterface - Returns the connection
     */
    public static function connection() {
        return self::$connection;
    }

    /**
     * Returns Database connection instance
     */
    public static function dbc() {
        return self::$connection->dbc();
    }

    /**
     * @return bool - Returns whether the user is connected to the Database
     */
    public static function isConnected() {
        return self::$connection == null ? false : self::$connection->isConnected();
    }

    /**
     * @param string $table
     * Adds table class to $tables
     */
    public static function add($table) {
        array_push(self::$tables, $table);
    }

    /**
     * Returns a list of table classes
     */
    public static function tables() {
        return self::$tables;
    }

    /**
     * Returns a list of the table instances
     */
    public static function tableInstances() {
        return self::$tables_instances;
    }

    /**
     * Returns a list of all the table names
     */
    public static function tablesByName() {
        return self::$table_names;
    }

    /**
     * Adds the table to a list of table instances
     * @param string $table
     */
    public static function addTableInstance($table) {
        /** @var Table $t */
        $t = new $table();

        self::$tables_instances[$t::getName()] = $t;
        array_push(self::$table_names, $t::getName());
    }

    public static function addReferenceTable($table) {
        array_push(static::$reference_tables, $table);
    }

    public static function getReferenceTables() {
        return static::$reference_tables;
    }

    /**
     * Creates the users table if they don't already exist
     * @param Table $table
     * @throws DatabaseTableError - Thrown is table does not exist
     */
    public static function createTableIfNotExists($table) {
        /** @var Table $t */
        if(self::$connection->num_rows(self::$connection->query("SHOW TABLES LIKE {$table::getName()}"))) {
            $t = new $table();
            self::$tables_instances[$t::getName()] = $t;
            array_push(self::$table_names, $t::getName());
        } else {
            $t = new $table();
            $t->addToDatabase();
            self::$tables_instances[$t::getName()] = $t;
            array_push(self::$table_names, $t::getName());
        }
    }

    /**
     * Gets a table from the Database
     * @param $table - Table to return
     * @return Table - Returns the table object
     * @throws TableNotFound - Thrown is the table is not found in the instance array
     */
    public static function table($table) {
        if(array_key_exists($table, self::$tables_instances)) {
            return self::$tables_instances[$table];
        } else {
            throw new TableNotFound("Table '$table' was not found in Database");
        }
    }

    /**
     * Returns the auth_user table instance
     * @return Table - Returns the 'Framework\Security\Auth\Models\User' model
     */
    public static function getAuthUserTable() {
        return self::$tables_instances["auth_user"];
    }

    /**
     * Returns the auth_permission table instance
     * @return Table - Returns the 'Framework\Security\Auth\Models\Permission' model
     */
    public static function getAuthPermissionTable() {
        return self::$tables_instances["auth_permission"];
    }

    /**
     * Returns the auth_group table instance
     * @return Table - Returns the 'Framework\Security\Auth\Models\Group' model
     */
    public static function getAuthGroupTable() {
        return self::$tables_instances["auth_group"];
    }

    /**
     * @param string $sql - Raw SQL code that the user want to execute on the Database instead of specific table
     * @return array | null - Returns the result if any, else it returns null
     * @throws SQLQueryError - Thrown if there is an error in the users SQl code
     */
    public static function raw($sql) {
        $q = self::$connection->query($sql);

        if($q === false)
            //throw new ColumnNotFoundError("Error given column(s) do not exist in table");
            throw new SQLQueryError(self::$connection->error());
        elseif(self::$connection->num_rows($q) == 0)
            return null;
        else
            return self::$connection->fetch_all($q, ResultTypes::ASSOC);
    }

    /**
     * Displays the tables in the actual Database
     */
    public static function tablesInDatabase() {
        if(sizeof(self::$connection->fetch_all(self::$connection->query("SHOW TABLES"))) == 0) {
            return [];
        }

        return explode(
            ",",
            implode(
                ",",
                call_user_func_array("array_merge",
                    self::$connection->fetch_all(self::$connection->query("SHOW TABLES"))
                )
            )
        );
    }

}