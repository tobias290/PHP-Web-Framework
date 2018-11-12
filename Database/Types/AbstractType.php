<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:35
 */

namespace Framework\Database\Types;


/**
 * Class Type
 * Base class to represent a SQL data type
 * @package Framework\Database
 */
abstract class AbstractType implements DatabaseTypeInterface {
    protected $type_sql_value;
    protected $form_class;
    protected $options = array();
    private $option_types = ["primary_key", "not_null", "unique", "default"];

    public function __construct($options=[]) {
        $this->options = $options;
        $this->setOptions();
    }

    private function setOptions() {
        foreach ($this->option_types as $option) {
            if(!array_key_exists($option, $this->options)) {
                $this->options[$option] = null;
            }

            // If the type is a primary key then not null must be added
            if($this->options["primary_key"]) $this->options["not_null"] = true;
        }
    }

    /**
     * @return array - Returns the all the options at once
     */
    public function getOptions(): array {
        return $this->options + ["type" => $this->type_sql_value];
    }

    /**
     * @inheritdoc
     */
    public function getType() {
        // Use 'static' keyword due to "Late Static Bindings"
        return $this->type_sql_value;
    }

    /**
     * @inheritdoc
     */
    public function getFormClass() {
        return $this->form_class;
    }

    /**
     * Returns whether field is to be 'NOT NULL'
     */
    public function isPrimaryKey() {
        // Use 'static' keyword due to "Late Static Bindings"
        return $this->options["primary_key"];
    }

    /**
     * Returns whether field is to be 'NOT NULL'
     */
    public function isNotNull() {
        // Use 'static' keyword due to "Late Static Bindings"
        return $this->options["not_null"];
    }

    /**
     * Returns whether field is to be 'UNIQUE'
     */
    public function isUnique() {
        // Use 'static' keyword due to "Late Static Bindings"
        return $this->options["unique"];
    }

    /**
     * Returns whether field is has a default value
     */
    public function hasDefault() {
        // Use 'static' keyword due to "Late Static Bindings"
        return !is_null($this->options["default"]);
    }

    /**
     * Returns the default value
     */
    public function getDefault() {
        // Use 'static' keyword due to "Late Static Bindings"
        return $this->options["default"];
    }

    /**
     * Creates a column in SQL with all appropriate options
     * @param string $name - Name of column
     * @param bool $is_next - Specifies whether there will be another column after current_column
     * @return string - Returns the SQL for the column
     */
    public function getSQL($name, $is_next) {
        return "$name " . $this->getType() .
            ($this->isPrimaryKey() ? " PRIMARY KEY" : "") .
            ($this->isNotNull() ? " NOT NULL" : "") .
            ($this->isUnique() ? " UNIQUE" : "") .
            ($this->hasDefault() ? " DEFAULT '" . $this->getDefault() . "'" : "") .
            ($is_next ? ", " : ");");
    }
}