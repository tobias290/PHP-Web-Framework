<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 20/05/2017
 * Time: 12:10
 */

namespace Framework\Database;

use Framework\Database\Connections\ResultTypes;
use Framework\Database\Relationships\{
    AbstractRelationship, ForeignKey, ManyToMany, OneToOne
};
use Framework\Database\Types\{
    AbstractType, Integer
};
use Framework\Exceptions\{
    DatabaseTableError, MultipleResultsError, SQLQueryError
};
use Framework\Forms\Form;

/**
 * Class Table
     * Main class for a table instance
     * Querying, Inserting, Updating is done via this class
 * @package Framework\Database
 */
abstract class Table {
    /**
     * @var string
     *
     * Name of this table in the Database
     */
    protected static $table_name;

    /**
     * @var Integer
     *
     * Represents the id column for this table
     * This is automatically created therefore the user doesn't need to create it
     * This column is a primary key, auto incremented and is required
     */
    public $id;

    /**
     * Table constructor that must be implemented.
     */
    public function __construct() {
        $this->id = new Integer([
            "auto_increment" => true,
            "primary_key" => true,
            "not_null" => true
        ]);
    }

    /**
     * Adds a table to the Database
     * @throws DatabaseTableError - thrown when there is an error creating the table
     */
    final public function addToDatabase() {
        // FIXME: currently id is created last possible fix is to remove the id field then re-add it to the start of the array
        $fields = $this->fields();

        $sql = "CREATE TABLE IF NOT EXISTS " . static::$table_name . " (";

        $foreign_keys = [];
        $many_to_many_relationships = [];

        /** @var AbstractType | \Framework\Database\Integer | AbstractRelationship | ForeignKey | OneToOne | ManyToMany $value */
        foreach ($fields as $name => $value) {
            // FIXME: error when ending on many to many as it doesn't close the SQL properly
            if(next($fields)) {
                if(($value instanceof ForeignKey) or ($value instanceof OneToOne)) {
                    // Creates SQL differently if it is a foreign key as a different $value is a different object
                    $foreign_keys[$name] = $value;

                    $sql .= $value->getType()->getSQL($name, true);
                } elseif($value instanceof ManyToMany) {
                    $many_to_many_relationships[$name] = $value;
                } else {
                    $sql .= $value->getSQL($name, true);
                }
            } else {
                if(($value instanceof ForeignKey) or ($value instanceof OneToOne)) {
                    // Creates SQL differently if it is a foreign key as a different $value is a different object
                    $foreign_keys[$name] = $value;

                    $sql .= $value->getType()->getSQL($name, !empty($foreign_keys));
                } elseif($value instanceof ManyToMany) {
                    $many_to_many_relationships[$name] = $value;
                    // Removes trailing white space then removes unneeded comma

                    if(empty($foreign_keys)) {
                        $sql = rtrim(rtrim($sql), ",");
                        $sql .= ");";
                    }
                } else {
                    $sql .= $value->getSQL($name, !empty($foreign_keys));
                }
            }
        }

        // Loops over all the foreign keys and adds them add the end of the DDL script
        foreach ($foreign_keys as $name => $foreign_key) {
            /** @var ForeignKey $foreign_key */
            $sql .= $foreign_key->getSQL($name, next($foreign_keys));
        }

        if(DB::connection()->query($sql) !== true) {
            throw new DatabaseTableError("Error creating table '" . static::$table_name . "': " . DB::connection()->error());
        }

        // Loops over each many to many and creates the reference table
        /** @var ManyToMany $many_to_many */
        foreach ($many_to_many_relationships as $name => $many_to_many) {
            $sql = "\n{$many_to_many->getSQL($name, null)} \n";

            if(DB::connection()->query($sql) !== true) {
                throw new DatabaseTableError("Error creating table '" . $name . "': " . DB::connection()->error());
            }
        }

        return;
    }

    /**
     * Returns table name
     */
    final public static function getName() {
        return static::$table_name;
    }

    /**
     * Returns the class name
     */
    final public function class() {
        return static::class;
    }

    final public function fields() {
        $fields = get_object_vars($this);
        unset($fields["table_name"]);
        unset($fields["foreign_keys"]);

        // Moves id to the start of the array so it's created first
        $fields = array("id" => $fields["id"]) + $fields;

        return $fields;
    }

    /**
     * @return array - Returns list of foreign keys and One to One relationships as [column_name => table_reference]
     */
    final public function getForeignKeys() {
        $foreign_keys = [];

        foreach ($this->fields() as $name => $value) {
            if(($value instanceof ForeignKey) or ($value instanceof OneToOne)) {
                $foreign_keys[$name] = $value->getTable();
            }
        }

        return $foreign_keys;
    }

    /**
     * @return array - Returns list of Many to many relationships as [column_name => table_reference]
     */
    final public function getManyToManyRelationships() {
        $many_to_many_relationships = [];

        foreach ($this->fields() as $name => $value) {
            if($value instanceof ManyToMany) {
                /* Creates a array element that contains:
                    * The name of field
                    * The table it references
                    * The name of the table that joins the 2 together
                */
                $many_to_many_relationships[$name] = ["name" => $name, "table" => $value->getTable(), "joining_table" => $value->getJoiningTableName()];
            }
        }

        return $many_to_many_relationships;
    }

    /**
     * Returns whether a given field exists in the table
     * @param string $field - Field name to search for
     * @return bool - Returns whether the given field exists in the table
     */
    final public function hasField($field) {
        return property_exists(static::class, $field);
    }

    // ------------------------------------------- QUERY METHODS -------------------------------------------------------

    /**
     * Replaces the first '?' with a value
     * @param $search - Character to search for
     * @param $value - Value to replace $search with
     * @param $query - New query after value was inserted
     * @return mixed - Returns the query after inserting new value
     */
    private function findFirst($search, $value, $query) {
        $search = '/' . preg_quote($search, '/') . '/';
        $value = "'$value'";
        return preg_replace($search, $value, $query, 1);
    }

    /**
     * Replaces all the '?'s in the query with Values
     * @param string $condition - Query to replace Values in
     * @param array $values - Values to replace '?'s with
     * @return mixed - Query with inserted Values
     */
    private function insertValuesIntoQuery($condition, $values) {
        foreach ($values as $value) {
            $condition = $this->findFirst("?", $value, $condition);
        }
        return $condition;
    }

    /**
     * Method to run SQL code and possibly return results
     * @param string $sql - SQL code to run
     * @param array $values - Values to insert into SQL
     * @param bool $return - Whether a result needs to be returned
     * @return array|bool|\mysqli_result|null - Either return the result as associate array or nothing
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    private function runSQLQuery($sql, $values, $return=true) {
        if(!empty($values)) $sql = $this->insertValuesIntoQuery($sql, $values);

        $q = DB::connection()->query($sql);

        if($q === false)
            //throw new ColumnNotFoundError("Error given column(s) do not exist in table");
            throw new SQLQueryError(DB::connection()->error());
        elseif(DB::connection()->num_rows($q) == 0)
            return null;
        elseif($return)
            return DB::connection()->fetch_all($q, ResultTypes::ASSOC);
        else
            return null;
    }

    /**
     * Takes the results from a query and inserts and data from foreign key references
     * @param $results - Results to search
     * @return array - Returns new results with inserted data
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    private function getDataFromRelationships($results) {
        // Foreign keys to search for
        $foreign_keys = $this->getForeignKeys();
        // Foreign keys to search for
        $many_to_many_relationships = $this->getManyToManyRelationships();

        // New results with inserted data
        $new_result = [];

        foreach ($results as $i => $result) {
            // The current entity being searched
            $current = [];
            foreach ($result as $key => $value) {
                if(in_array($key, array_keys($foreign_keys))) {
                    // If is foreign key gets the data it references from the correct table
                    // NOTE: this had to be done this way is trying to get the table from 'DB' and running the 'get()' method got us a fatal warning about redeclaring the 'find_first()' function
                    $data = $this->runSQLQuery("SELECT * FROM $foreign_keys[$key] WHERE id = $value", [], true);

                    // Replaces the reference ID with the reference data
                    $current[$key] = $data[0];
                } else {
                    // If not a foreign key just insert the current data as the new data
                    $current[$key] = $value;
                }
            }

            foreach ($many_to_many_relationships as $relationship) {
                // Gets list of related columns
                $ids = DB::raw("SELECT " . $relationship["table"] . "_id" . " FROM " . $relationship["joining_table"] . " WHERE " . static::$table_name. "_id" . " = " . $result["id"] . " ORDER BY ". $relationship["table"] . "_id" . " ASC");

                // If there is no links then insert empty array and continue
                if($ids == null) {
                    $current[$relationship["name"]] = [];
                    continue;
                }

                // Gets a list of entities between the lowest ID and the highest ID
                $data = DB::raw("SELECT * FROM " . $relationship["table"] . " WHERE id BETWEEN " . $ids[0][$relationship["table"] . "_id"] . " AND " . end($ids)[$relationship["table"] . "_id"]);
                $current[$relationship["name"]] = $data;
            }

            // Push current entity to new list
            array_push($new_result, $current);
        }

        return $new_result;
    }

    /**
     * Adds a new column to the table
     * @param string $column - Name of column to add
     * @param string $data_type - Data type for column
     * @param array $options - Array of other options to add to table e.g NOT NULL
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    final public function addColumn($column, $data_type, $options=[]) {
        $sql = "ALTER TABLE " . static::$table_name . " ADD $column $data_type " . implode(" ", $options);

        $this->runSQLQuery($sql, null, $return=false);
    }

    /**
     * Alter a column already existing in the table
     * @param string $column - Name of column to alter
     * @param string $data_type - Data type for column
     * @param array $options - Array of other options to add to table e.g NOT NULL
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    final public function alterColumn($column, $data_type, $options=[]) {
        $sql = "ALTER TABLE " . static::$table_name . " MODIFY COLUMN $column $data_type " . implode(" ", $options);

        $this->runSQLQuery($sql, null, $return=false);
    }

    /**
     * Drops a column in the table
     * @param string $column - Name of column to drop
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    final public function dropColumn($column) {
        $sql = "ALTER TABLE " . static::$table_name . " DROP COLUMN $column";

        $this->runSQLQuery($sql, null, $return=false);
    }

    /**
     * Returns the average of the given column
     * @param string $count - Column to find the average for
     * @param string | null $condition - Condition to get results
     * @param array | null $values - Values to insert into condition
     * @return string - As the result is a single number it returns a data entity
     * @throws SQLQueryError - Thrown either $column or $value and the other isn't
     */
    final public function average($count, $condition=null, $values=null) {
        if(empty($condition) and empty($values)) {
            $sql = "SELECT AVG($count) FROM ". static::$table_name;
        } elseif(empty($condition) and !empty($values) or !empty($condition) and empty($values)){
            throw new SQLQueryError("Either column and value both must be empty or neither must be empty");
        } else {
            $sql = "SELECT AVG($count) FROM ". static::$table_name . " WHERE $condition";
        }

        $result = $this->runSQLQuery($sql, $values);

        return $result[0]["AVG($count)"];
    }

    /**
     * Returns the count of the given column
     * @param string $count - Column to find the count for
     * @param string | null $condition - Condition to get results
     * @param array | null $values - Values to insert into condition
     * @return string - As the result is a single number it returns a data entity
     * @throws SQLQueryError - Thrown either $column or $value and the other isn't
     */
    final public function count($count, $condition=null, $values=null) {
        if(empty($condition) and empty($values)) {
            $sql = "SELECT COUNT($count) FROM ". static::$table_name;

        } elseif(empty($condition) and !empty($values) or !empty($condition) and empty($values)){
            throw new SQLQueryError("Either column and value both must be empty or neither must be empty");
        } else {
            $sql = "SELECT COUNT($count) FROM ". static::$table_name . " WHERE $condition";
        }

        $result = $this->runSQLQuery($sql, $values);

        return $result[0]["COUNT($count)"];
    }

    /**
     * Deletes a row from the table
     * @param string $condition - Condition to get results
     * @param array $values - Values to insert into condition
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    final public function delete($condition, $values) {
        $sql = "DELETE FROM " . static::$table_name . " WHERE $condition";

        $this->runSQLQuery($sql, $values, $return=false);
    }

    /**
     * @param string $condition - Condition to get results
     * @param array $values - Values to insert into condition
     * @param array $retrieve - Columns to retrieve from query
     * @return Result - Returns the results as a new result object
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    final public function distinct($condition, $values, $retrieve=["*"]) {
        $sql = "SELECT DISTINCT " . implode($retrieve, ",") ." FROM " . static::$table_name . " WHERE $condition";

        $results = $this->runSQLQuery($sql, $values);

        return new Result(static::$table_name, $this->getDataFromRelationships($results));
    }

    /**
     * Drops the table from the Database
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    final public function drop() {
        $sql = "DROP TABLE " . static::$table_name;

        $this->runSQLQuery($sql, null, $return=false);
    }

    /**
     * Returns a single result from query
     * @param string $condition - $query to preform
     * @param array $values - Values to replace '?'s with in $query
     * @param array $retrieve - Columns to retrieve from query
     * @return DataEntity - Returns a new data entity as there is only 1 result
     * @throws SQLQueryError - Thrown when given column does not exist
     * @throws MultipleResultsError - As this should only return 1 object, this is thrown is multiple objects are collected from SQL query
     */
    final public function get($condition, $values, $retrieve=["*"]) {
        $condition = $this->insertValuesIntoQuery($condition, $values);

        $sql = "SELECT " . implode($retrieve, ",") . " FROM " . static::$table_name . " WHERE $condition";

        $q = DB::connection()->query($sql);

        if($q === false)
            throw new SQLQueryError(DB::connection()->error());
        elseif(DB::connection()->num_rows($q) == 0)
            return new DataEntity(static::$table_name, null);
        elseif(DB::connection()->num_rows($q) > 1)
            throw new MultipleResultsError("'get()' can only return 1 result");
        else
            $result = DB::connection()->fetch_all($q, MYSQLI_ASSOC);

        return new DataEntity(static::$table_name, $this->getDataFromRelationships($result)[0]);
    }

    /**
     * Returns all rows from table
     * @param array $retrieve - Columns to retrieve from query
     * @return Result - Returns a new result object of all object retrieved
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    final public function getAll($retrieve=["*"]) {
        $sql = "SELECT " . implode($retrieve, ",") ." FROM " . static::$table_name;

        $results = $this->runSQLQuery($sql, null);

        return (
            !empty($results) ?
            new Result(static::$table_name, $this->getDataFromRelationships($results)) :
            new Result(static::$table_name, [])
        );
    }

    /**
     * Returns multiple results from a query
     * @param string $condition - Condition to get results
     * @param array $values - Value to insert into the condition
     * @param array $retrieve - Columns to retrieve from query
     * @return Result - Returns a new result object of all object retrieved
     * @throws SQLQueryError - Thrown when given column does not exist
     */
    final public function filter($condition, $values, $retrieve=["*"]) {
        $sql = "SELECT " . implode($retrieve, ",") ." FROM " . static::$table_name . " WHERE $condition;";

        $results = $this->runSQLQuery($sql, $values);

        return new Result(static::$table_name, $this->getDataFromRelationships($results));
    }

    /**
     * Inserts a new row into the table
     * @param array $data - List of key value pairs containing the columns and their Values
     * @param bool $all - If this is true then you are inserting into every column therefore the column names do not need to be specified.
     *                  - Therefore you only need to pass the Values in the $data array not column=value
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    final public function insert($data, $all=false) {
        if($all)
            $sql = "INSERT INTO " . static::$table_name . " VALUES ( '" . implode($data, "', '") . "')";
        else
            $sql = "INSERT INTO " . static::$table_name . " (" . implode(array_keys($data), ", ") . " ) VALUES ( '" . implode(array_values($data), "', '") . "')";

        $this->runSQLQuery($sql, null, $return=false);
    }

    /**
     * Removes all fields in the form data that are not in the Database
     * @param array $data - Form data
     */
    private function removeNonDatabaseFields(&$data) {
        $fields = get_object_vars($this);
        unset($fields["table_name"]);

        foreach (array_keys($data) as $data_key) {
            if (!key_exists($data_key, $fields)) {
                unset($data[$data_key]);
            }
        }
    }

    /**
     * @param Form $form - Form with data to insert
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    final public function insertFromForm($form) {
        $data = $form->getData();

        $this->removeNonDatabaseFields($data);

        $this->insert($data);
    }

    /**
     * Returns the max of the given column
     * @param string $count - Column to find the sum for
     * @param string | null $condition - Condition to get results
     * @param array | null $values - Values to insert into the condition
     * @return string - As the result is a single number it returns a data entity
     * @throws SQLQueryError - Thrown either $column or $value and the other isn't
     */
    final public function max($count, $condition=null, $values=null) {
        if(empty($condition) and empty($values)) {
            $sql = "SELECT MAX($count) FROM ". static::$table_name;

        } elseif(empty($condition) and !empty($values) or !empty($condition) and empty($values)){
            throw new SQLQueryError("Either column and value both must be empty or neither must be empty");
        } else {
            $sql = "SELECT SUM($count) FROM ". static::$table_name . " WHERE $condition";
        }

        $result = $this->runSQLQuery($sql, $values);

        return $result[0]["MAX($count)"];
    }

    /**
     * Returns the min of the given column
     * @param string $count - Column to find the sum for
     * @param string | null $condition - Condition to get results
     * @param array | null $values - Values to insert into the condition
     * @return string - As the result is a single number it returns a data entity
     * @throws SQLQueryError - Thrown either $column or $value and the other isn't
     */
    final public function min($count, $condition=null, $values=null) {
        if(empty($condition) and empty($values)) {
            $sql = "SELECT MIN($count) FROM ". static::$table_name;

        } elseif(empty($condition) and !empty($values) or !empty($condition) and empty($values)){
            throw new SQLQueryError("Either column and value both must be empty or neither must be empty");
        } else {
            $sql = "SELECT SUM($count) FROM ". static::$table_name . " WHERE $condition";
        }

        $result = $this->runSQLQuery($sql, $values);

        return $result[0]["MIN($count)"];
    }

    /**
     * Returns a EntityMaker which is used to create a new entity for the table
     * @param Form $form - Form to create an entity (Saves manually entering each property)
     * @return EntityMaker
     */
    final public function new($form=null) {
        $maker =  new EntityMaker($this);

        if(!empty($form)) $maker->form($form);

        return $maker;
    }

    /**
     * Allows the user to run their own SQL code
     * @param $sql - Raw SQL to run
     * @return array|bool|\mysqli_result|null - Returns the results as a new result object
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    final public function raw($sql) {
        $results = $this->runSQLQuery($sql, null);

        return $results;
    }

    /**
     * Returns the sum of the given column
     * @param string $count - Column to find the sum for
     * @param string | null $condition - Condition to get results
     * @param array | null $values - Values to insert into the condition
     * @return string - As the result is a single number it returns a data entity
     * @throws SQLQueryError - Thrown either $column or $value and the other isn't
     */
    final public function sum($count, $condition=null, $values=null) {
        if(empty($condition) and empty($values)) {
            $sql = "SELECT SUM($count) FROM ". static::$table_name;
        } elseif(empty($condition) and !empty($values) or !empty($condition) and empty($values)){
            throw new SQLQueryError("Either column and value both must be empty or neither must be empty");
        } else {
            $sql = "SELECT SUM($count) FROM ". static::$table_name . " WHERE $condition";
        }

        $result = $this->runSQLQuery($sql, $values);

        return $result[0]["SUM($count)"];
    }

    /**
     * Shows the columns from this table
     * @return array|bool|\mysqli_result|null
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    final public function showColumns() {
        $sql = "SHOW COLUMNS FROM " . static::$table_name;

        return $this->runSQLQuery($sql, []);
    }

    /**
     * Deletes all the content in the table but not the table itself
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    final public function truncate() {
        $sql = "TRUNCATE TABLE " . static::$table_name;

        $this->runSQLQuery($sql, null, $return=false);
    }

    /**
     * Updates a row in the table
     * @param string $set - String of columns to '?'s to be replaced with values
     * @param array $values - values to set each column to
     * @param string $condition - 'WHERE' condition to know which row to update
     * @param array $condition_values - Values to replaces '?'s in $condition with
     * @throws SQLQueryError - Thrown if given columns do not exist in table
     */
    final public function update($set, $values, $condition, $condition_values) {
        $sql = "UPDATE " . static::$table_name . " SET $set WHERE $condition";

        $this->runSQLQuery($sql, array_merge($values, $condition_values), $return=false);
    }
}