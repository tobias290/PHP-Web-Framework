<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 15/06/2017
 * Time: 13:52
 */

namespace Framework\Console\Commands\Database;


use Framework\Console\Commands\Command;
use Framework\Database\Migration;

final class MigrationCommand extends Command {
    protected static $name = "migrate {table?}";
    protected static $description = "Updates a table";
    protected static $help = "* When run it will update a table columns \n* For example if a column was added or removed";

    /**
     * Creates a migration instance and executes a new migration
     */
    public function execute() {
        if(!empty($this->getArg("table", $crash=false)))
            $this->migrateTable($this->getArg("table"));
        else
            $this->migrateDatabase();
    }

    /**
     * Migrates a specific table
     * E.g. A new column was added to a table
     *
     * @param $table - Table name
     * @throws \Framework\Exceptions\ColumnNotFound
     * @throws \Framework\Exceptions\ConfigPropertyIncorrect
     * @throws \Framework\Exceptions\SQLQueryError
     * @throws \Framework\Exceptions\TableNotFound
     */
    private function migrateTable($table) {
        $migration = new Migration($table);
        $migration->updateExistingTable();
    }

    /**
     * Migrates the entire Database
     * E.g. If a new table was added
     */
    private function migrateDatabase() {
        $migration = new Migration();
        $migration->updateDatabase();
    }
}