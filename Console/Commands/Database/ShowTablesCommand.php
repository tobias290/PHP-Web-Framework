<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 17/06/2017
 * Time: 11:53
 */

namespace Framework\Console\Commands\Database;


use Framework\Config;
use Framework\Console\Commands\Command;
use Framework\Database\DB;
use Framework\Helpers\StringFunctions;

/**
 * Class ShowTablesCommand
    * Prints out all the tables in the users Database
 * @package Framework\Console\Commands
 */
final class ShowTablesCommand extends Command {
    // Using 'stringTable()'
    use StringFunctions;

    protected static $name = "tables";
    protected static $description = "Lists all the tables in the Database";
    protected static $help = "Lists all the tables in the Database";

    public function execute() {
        $db_name = Config::instance()->database->database;
        $tables = DB::tablesInDatabase();

        $this->stringTable($tables, $db_name);
    }
}