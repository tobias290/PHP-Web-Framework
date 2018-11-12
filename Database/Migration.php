<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 30/05/2017
 * Time: 11:25
 */

namespace Framework\Database;

use Framework\Config;
use Framework\Database\Relationships\{
    AbstractRelationship, ForeignKey, OneToOne
};
use Framework\Database\Types\AbstractType;
use Framework\Exceptions\{
    ColumnNotFound,
    ConfigPropertyIncorrect,
    KeyNotFoundException,
    SQLQueryError,
    TableNotFound
};
use Framework\Logging\Logger;

/**
 * Class Migration
    * Handles all migrations related to Database tables
 * @package Framework\Database
 */
final class Migration {
    /**
     * @var Table|null
     */
    private $table = null;
    private $table_name = null;

    private $log_changes;

    public function __construct($table_name=null) {
        if($table_name != null) {
            $this->table = DB::table($table_name);
            $this->table_name = $table_name;
        }

        try {
            if (!Config::instance()->log->log_migration)
                $this->log_changes = false;
            elseif (Config::instance()->log->log_migration)
                $this->log_changes = true;
            else
                throw new ConfigPropertyIncorrect("Property 'log_migration' can only be set to true or false");
        } catch (KeyNotFoundException $e) {
            $this->log_changes = false;
        }
    }

    /**
     * NOTE: code seems very inefficient
     * Updates the entire Database
     * First it updated all tables in the Database
     * Then it checks for any new tables and adds them to the Database
     * Then it checks for any removed table and drops them from the Database
     */
    public function updateDatabase() {
        // All the extra function remove an unnecessary layer of arrays
        $tables_in_database = DB::tablesInDatabase();

        // List of matched tables
        $matched = [];

        /** @var Table $table */
        foreach (DB::tableInstances() as $i => $table) {
            $this->table = $table;
            $this->table_name = $table::getName();

            if(in_array($this->table_name, $tables_in_database)) {
                array_push($matched, $table->class());

                // Go through and check each table
                $this->updateExistingTable();
            }
        }

        // Loops over to see if any new tables have appeared and if so add them
        foreach (array_diff(DB::tables(), $matched) as $table) {
            DB::createTableIfNotExists($table);

            echo "Created $table \n";
            if($this->log_changes) Logger::migration("Created $table");
        }

        // Loops over and find tables defined in the Database but not added to the DB::$table_instances array
        // If found it removes them from the Database
        foreach (array_diff($tables_in_database, DB::tablesByName()) as $table) {
            // Checks for reference tables used in Many to Many relationships
            // If found continue as they should not be dropped
            if(in_array($table, DB::getReferenceTables())) continue;

            // FIXME: drop reference table first to stop foreign key constraint error

            $sql = "DROP TABLE $table";

            $q = DB::connection()->query($sql);

            if($q === false) {
                //throw new ColumnNotFoundError("Error given column(s) do not exist in table");
                throw new SQLQueryError(DB::connection()->error());
            } else {
                echo "Dropped $table \n";
                if ($this->log_changes) Logger::migration("Dropped $table");
            }
        }
    }

    /**
     * Method updates an existing table
     * @throws ColumnNotFound
     * @throws SQLQueryError
     * @throws TableNotFound - Thrown if the table was not specified
     */
    public function updateExistingTable() {
        echo "Checking $this->table_name \n";
        echo "______________________________\n";

        if($this->table == null) throw new TableNotFound("No table was specified");

        $fields = get_object_vars($this->table);
        unset($fields["table_name"]);

        $results = $this->table->showColumns();
        // Represents all columns in the Database that have been matched against the table definition columns
        $matched = [];

        /** @var AbstractType | ForeignKey $class */
        foreach ($fields as $name => $class) {

            if($this->columnExists($name, $results)) {
                $result = $this->getResult($name, $results);
                array_push($matched, $result["Field"]);

                $this->checkColumn($result, ["name" => $name, "options" => (!($class instanceof AbstractRelationship) ? $class->getOptions() : $class->getType()->getOptions())]);
            } else {
                $this->addColumn($name, $class);
            }
        }

        // Looks for any column that was not matches but exits in the SQL Database
        // This means the user has removed the column therefore it should be removed in the Database
        foreach (array_diff(array_column($results, "Field"), $matched) as $column) {
            $this->removeColumn($column);
        }
        echo "\n";
    }

    /**
     * Looks through the SQL Database columns for $name and returns the correct column
     * @param string $name - Name of the field to look for
     * @param array $results - List of table columns to look for result in
     * @return array - Returns the found result
     * @throws ColumnNotFound - Thrown the $name was not found in the $results
     */
    private function getResult($name, $results) {
        $fields = array_column($results, "Field");

        foreach ($fields as $i => $field) {
            if ($field == $name) return $results[$i];
        }
        throw new ColumnNotFound("Column '$name' not found in table");
    }

    /**
     * Searches an array of table columns to check if a specified column is in the Database
     * @param string $column_name - Name of the column to search for
     * @param $table_columns - List of table columns in the SQL Database
     * @return bool - Returns whether it was in the array or not
     */
    private function columnExists($column_name, $table_columns) {
        foreach ($table_columns as $column) {
            if($column_name == $column["Field"]) return true;
        }
        return false;
    }

    /**
     * Checks an existing column for any changes and if found apply them
     * @param array $current - Array with the current column's that is in the Database
     * @param array $new - New column's name and class to check against the current to see if there are any changes that need to be made
     *                   - ["name" => string, "options" => array of options]
     * @throws SQLQueryError
     */
    private function checkColumn($current, $new) {
        // For debugging purposes only to evaluate username field not id
        echo "checking column '" . $new["name"] . "' \n";

        /*
           Key - Value list of the corresponding key and true value in SQL column list

           framework value => [
               SQL value, value if true, Value in SQL code
           ]
        */
        $reference = [
            "primary_key" =>    ["name" => "Key",    "true_value" => "PRI",            "sql_code" => "PRIMARY KEY"],
            "auto_increment" => ["name" => "Extra",  "true_value" => "auto_increment", "sql_code" => "AUTO_INCREMENT"],
            "not_null" =>       ["name" => "Null",   "true_value" => "NO",             "sql_code" => "NOT NULL"],
            "unique" =>         ["name" => "Key",    "true_value" => "UNI",            "sql_code" => "UNIQUE"],
            //"default" =>        ["name" => "Default" "true_value" => "",               "sql_code" => ""],
            ];

        // TODO: allow modification to default, and a few others

        $new_options = [];

        foreach ($new["options"] as $option => $value) {
            // Any new data type are automatically changed on line 123
            if ($option == "type" or $option == "default" or $option == "length") continue;
            // If the true value for the option is not what is currently set and in the users option it is true add it to $new_options list
            if($value and $current[$reference[$option]["name"]] != $reference[$option]["true_value"]) {
                // If changed to true
                $new_options[] = $reference[$option]["sql_code"];
            } elseif (!$value and $current[$reference[$option]["name"]] == $reference[$option]["true_value"]) {
                // If changed to false
                $new_options[] = $reference[$option]["sql_code"];
            }
        }

        if(!empty($new_options)) {
            $this->table->alterColumn($new["name"], $new["options"]["type"], $new_options);
            if($this->log_changes) Logger::migration("Altered column {$new['name']}");
        }
    }

    /**
     * Adds a new column to the table
     * @param string $name - Name of the new column
     * @param AbstractType | \Framework\Database\Types\Integer | ForeignKey $column - Class of the new column
     * @throws SQLQueryError
     */
    private function addColumn($name, $column) {
        echo "adding column '$name' \n";

        $options = [];

        if(!($column instanceof AbstractRelationship)) {
            $column->isPrimaryKey() ? $options[] = "PRIMARY KEY" : null;
            $column->isNotNull() ? $options[] = "NOT NULL" : null;
            $column->isUnique() ? $options[] = "UNIQUE" : null;
            $column->getType() == "INT" && $column->isAutoIncrement() ? $options[] = "AUTO_INCREMENT" : null;
            $column->hasDefault() ? $options[] = "DEFAULT = '" . $column->getDefault() . "'" : null;

            $this->table->addColumn($name, $column->getType(), $options);
        } else {
            $column->getType()->isPrimaryKey() ? $options[] = "PRIMARY KEY" : null;
            $column->getType()->isNotNull() ? $options[] = "NOT NULL" : null;
            $column->getType()->isUnique() ? $options[] = "UNIQUE" : null;
            $column->getType()->getType() == "INT" && $column->getType()->isAutoIncrement() ? $options[] = "AUTO_INCREMENT" : null;
            $column->getType()->hasDefault() ? $options[] = "DEFAULT = '" . $column->getType()->getDefault() . "'" : null;

            $this->table->addColumn($name, $column->getType()->getType(), $options);
        }


        if($this->log_changes) Logger::migration("Added column $name of type {$column->getType()}");
    }

    /**
     * @param string $name - Name of the column to remove from the table
     * @throws SQLQueryError
     */
    private function removeColumn($name) {
        echo "removing column '$name' \n";

        $this->table->dropColumn($name);

        if($this->log_changes) Logger::migration("Dropped table $name");
    }
}