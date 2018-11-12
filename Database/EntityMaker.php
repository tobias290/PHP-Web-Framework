<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 23/06/2017
 * Time: 20:43
 */

namespace Framework\Database;


use Framework\Exceptions\{ColumnNotFound,SQLQueryError};
use Framework\Forms\Form;

/**
 * Class EntityMaker
    * Class to make a new entity for a Database table
 * @package Framework\Database
 */
final class EntityMaker {
    /**
     * @var Table
     *
     * Instance of the table that this entity maker is for
     */
    private $table_instance;

    /**
     * @var array
     *
     * List of data to set
     */
    private $data = [];

    public function __construct($table_instance) {
        $this->table_instance = $table_instance;
    }

    /**
     * Returns a column from the data list
     * @param $name - Name of column to get
     * @return mixed
     * @throws \Exception - Thrown if $name is not in data list
     */
    public function __get($name) {
        if(key_exists($name, $this->data))
            return $this->data[$name];
        else
            throw new \Exception("$name has not been set yet");
    }

    /**
     * @param string $name - Name of column
     * @param mixed $value - Value of column
     * @throws ColumnNotFound - Thrown if $name does not exist in the data list
     */
    public function __set($name, $value) {
        // TODO: check $value is correct type for its column

        if (in_array($name, array_keys($this->table_instance->fields()))) {
            // Field is not many to many therefore it can be set as normal
            $this->data[$name] = $value;
        } else {
            throw new ColumnNotFound("$name is not a column in '{$this->table_instance::getName()}'");
        }
    }

    /**
     * Saves the data to the Database
     */
    public function save() {
        $many_to_many_data = [];

        foreach ($this->data as $column_name => $datum) {
            // Loops over the data if any of them are an array it means it is a many to many relationship
            // Save to a separate array so the rest of the data can be inserted as normal
            if($this->fieldIsManyToMany($column_name)) {
                $many_to_many_data[$column_name] = $datum;
                unset($this->data[$column_name]);
            }
        }

        $this->table_instance->insert($this->data);

        foreach ($many_to_many_data as $column_name => $datum) {
            // Now loop over the many to many data and create it
            $this->setManyToManyData($column_name, $datum);
        }
    }

    /**
     * @return array - Returns the data array (as it is)
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Function takes all form elements and inserts them into the entity
     * @param Form $form
     */
    public function form($form) {
        $fields = $form->getFields();

        foreach ($fields as $field) {
            if(in_array($field, array_keys($this->table_instance->fields()))) {
                $this->data[$field] = $form->{$field}->getValue();
            }
        }
    }

    /**
     * Checks to see if the column has a many to many relationship
     * @param string $column - Name of the column to check
     * @return bool - Returns whether the column does have a many to many relationship
     */
    private function fieldIsManyToMany($column) {
        foreach ($this->table_instance->getManyToManyRelationships() as $column_name => $joining_reference) {
            if($column_name == $column) return true;
        }
        return false;
    }

    /**
     * Checks column to see if it has a many to many relationship, is so then it inserts multiple values into joining table
     * @param string $column - Name of column
     * @param array $values - Values to insert (if done correctly the it will be an array of 'DataEntity' classes)
     * @throws SQLQueryError - Thrown if there is an error in the users SQl code
     */
    private function setManyToManyData($column, $values) {
        /** @var DataEntity $value */
        foreach ($values as $value) {
            $table_name = $this->table_instance::getName() . "_id";
            $other_table_name = $value->getTableName() . "_id";
            $joining_name_name = $this->table_instance->fields()[$column]->getJoiningTableName();
            $id = $this->id;
            $other_id = $value->id;

            DB::raw("INSERT INTO $joining_name_name ($table_name, $other_table_name) VALUES ($id, $other_id)");
        }
    }
}