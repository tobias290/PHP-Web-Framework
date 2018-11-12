<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 21/05/2017
 * Time: 20:47
 */

namespace Framework\Database;


use Framework\Serializer\Serializable;

/**
 * Class Result
    * Class to represent the results from the SQL query
 * @package Framework\Database
 */
final class Result implements Serializable, \Countable {
    private $table_name;
    private $results;
    private $is_data_one_column = false;

    /**
     * Result constructor.
     * @param string $table_name - Name of the table in which these results are from
     * @param $results - Results from the SQL query
     */
    public function __construct($table_name, $results) {
        $this->table_name = $table_name;
        $this->results = $results;

        $this->checkIfOneResult();
        $this->convertArrayToDataEntities();
    }

    /**
     * Checks results to see if it is only one
     * If so create a single element
     */
    private function checkIfOneResult(){
        if(sizeof($this->results) == 1) {
            $this->is_data_one_column = true;
            $this->results = $this->results[0];
        }
    }

    /**
     * Loops over the results array and convert each sub-array to a 'DataEntity' object
     */
    private function convertArrayToDataEntities() {
        if($this->is_data_one_column and !($this->results instanceof DataEntity)) {
            $this->results = new DataEntity($this->table_name, $this->results);
        }

        foreach ($this->results as $i => $result) {
            // It is possible that a result is already a data entity
            // Therefore we don't want it to be a data entity in side a data entity
            if(!($result instanceof DataEntity))
                $this->results[$i] = new DataEntity($this->table_name, $result);
        }
    }

    /**
     * @return string - Returns the name of the table in which these results are from
     */
    public function getTableName() {
        return $this->table_name;
    }

    /**
     * @return bool - Returns whether the entity is empty or not
     */
    public function isEmpty() {
        return empty($this->results);
    }

    /**
     * @inheritdoc
     */
    public function asArray($name=null) {
        return ($name == null) ? $this->results : [$name => $this->results];
    }

    /**
     * @inheritdoc
     */
    public function asJson($name=null) {
        return ($name == null) ? json_encode($this->results) : json_encode([$name => $this->results]);
    }

    /**
     * @inheritdoc
     */
    public function asXml($name=null) {
        return ($name == null) ? xmlrpc_encode($this->results) : xmlrpc_encode([$name => $this->results]);
    }

    /**
     * Returns all the results
     */
    public function all() {
        return $this->results;
    }

    /**
     * @return DataEntity - Returns the first result
     */
    public function first() {
        return $this->is_data_one_column ? $this->results : $this->results[0];
    }

    /**
     * @return DataEntity - Returns the last result
     */
    public function last() {
        return $this->is_data_one_column ? $this->results : end($this->results);
    }

    /**
     * Orders the results according to a column (and either ASC or DESC)
     * @param string $column
     * @param bool $reversed
     * @return DataEntity | Result
     */
    public function orderBy($column, $reversed=false) {
        if($this->is_data_one_column)
            return $this->results;

        $result = $this->results;

        usort($result, function($a, $b) use ($column){
            return $a->{$column} - $b->{$column};
        });

        if ($reversed) $result = array_reverse($result);

        return new Result($this->table_name, $result);
    }

    /**
     * @param $limit - Number to limit results to
     * @return DataEntity | Result - Either returns the only entity or a new set of results
     */
    public function limit($limit) {
        return $this->is_data_one_column ? $this->results : new Result($this->table_name, array_slice($this->results, 0, $limit));
    }

    /**
     * Returns a result at a given index
     * @param integer $i - Index to find element
     * @return DataEntity - Returns a single result as a data entity
     */
    public function index($i) {
        return $this->is_data_one_column ? $this->results :$this->results[$i];
    }

    /**
     * Returns results where a given column equals (or a different operator) the value
     * @param string $column - Column to use to compare to $value
     * @param string $value - value to compare to $column
     * @param string $operator - operator to compare $column and $value
     * @return DataEntity | Result - Returns the data as another result object
     */
    public function where($column, $value, $operator="==") {
        if($this->is_data_one_column)
            return $this->results;

        $results = array();

        foreach ($this->results as $i => $object) {
            if($operator == "=") $operator = "==";
            $result = false;
            $col_name = $object->{$column};
            $str = "\$result = $col_name $operator $value;";
            @eval($str);
            if ($result) {
                array_push($results, $this->results[$i]);
            }
        }

        // Checks to see if the search only finds one result if so return it as a single 'DataEntity' class
        return (sizeof($results) == 1) ? $results[0] : new Result($this->table_name, $results);
    }

    // ______________________________________________ Countable ________________________________________________________

    /**
     * @inheritdoc
     */
    public function count() {
        return count($this->results);
    }

    // ___________________________________________ End  Countable ______________________________________________________
}