<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 22/05/2017
 * Time: 10:28
 */

namespace Framework\Database;

use Framework\Exceptions\CannotUnsetError;
use Framework\Exceptions\ColumnNotFound;
use Framework\Serializer\Serializable;

/**
 * Class DataEntity
    * A class to represent a single result as a entity
 * @package Framework\Database
 */
final class DataEntity implements Serializable, \ArrayAccess, \Countable, \Iterator {
    private $table_name;
    private $fields = array();

    /**
     * DataEntity constructor.
     * @param string $table_name - Name of the table in which these results are from
     * @param array $fields - Returned fields from the SQL query
     */
    public function __construct($table_name, $fields) {
        $this->table_name = $table_name;
        $this->fields = $fields;
    }

    /**
     * To to get a field dynamically from $fields property
     * @param string $key - property that is to be returned
     * @return string | boolean | array - property as string is returned
     * @throws ColumnNotFound - thrown is property is not found in $fields
     */
    public function __get($key) {
        if(isset($this->fields[$key]))
            return $this->fields[$key];
        else
            throw new ColumnNotFound("That column not does not exists");
    }

    /**
     * This updates a given value in the Database
     * @param string $name - Name of field to update
     * @param $value - New value of field
     * @throws \Framework\Exceptions\SQLQueryError - Thrown is there is an error with the SQL query
     * @throws \Framework\Exceptions\TableNotFound - Thrown in the table was not found
     */
    public function __set($name, $value) {
        $this->fields[$name] = $value;

        $table = DB::table($this->table_name);

        $table->update("$name = ?", [$value], "id = ?", [$this->id]);
    }

    // _____________________________________________ ArrayAccess _______________________________________________________

    /**
     * @inheritdoc
     */
    public function offsetGet($offset) {
        return $this->__get($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value) {
        $this->__set($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset) {
        throw new CannotUnsetError("Cannot unset data");
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset) {
        return isset($this->fields[$offset]);
    }

    // ___________________________________________ End ArrayAccess _____________________________________________________

    // ______________________________________________ Countable ________________________________________________________

    /**
     * @inheritdoc
     */
    public function count() {
        return count($this->fields);
    }

    // ___________________________________________ End  Countable ______________________________________________________

    // _____________________________________________ Iterator __________________________________________________________

    /**
     * @inheritdoc
     */
    public function current() {
        return current($this->fields);
    }

    /**
     * @inheritdoc
     */
    public function key() {
        return key($this->fields);
    }

    /**
     * @inheritdoc
     */
    public function next() {
        next($this->fields);
    }

    /**
     * @inheritdoc
     */
    public function rewind() {
        reset($this->fields);
    }

    /**
     * @inheritdoc
     */
    public function valid() {
        return key($this->fields) !== null;
    }

    // ____________________________________________ End Iterator _______________________________________________________

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
        return empty($this->fields);
    }

    /**
     * @inheritdoc
     */
    public function asArray($name=null) {
        return ($name == null) ? $this->fields : [$name => $this->fields];
    }

    /**
     * @inheritdoc
     */
    public function asJson($name=null) {
        return ($name == null) ? json_encode($this->fields) : json_encode([$name => $this->fields]);
    }

    /**
     * @inheritdoc
     */
    public function asXml($name=null) {
        return ($name == null) ? xmlrpc_encode($this->fields) : xmlrpc_encode([$name => $this->fields]);
    }
}