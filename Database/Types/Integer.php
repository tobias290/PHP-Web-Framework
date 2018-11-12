<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:36
 */

namespace Framework\Database\Types;

use Framework\Forms\Types\Number;


/**
 * Class Integer
 * Represents the SQL type 'INT'
 * @package Framework\Database
 */
final class Integer extends AbstractType {
    protected $type_sql_value = "INT";
    protected $form_class = Number::class;
    protected $auto_increment;

    /**
     * Integer constructor.
     * @param array $options - List of options for the column
     */
    public function __construct($options=[]) {
        parent::__construct($options);

        if(array_key_exists("auto_increment", $options))
            $this->auto_increment = $options["auto_increment"];
        else
            $this->auto_increment = false;
    }

    /**
     * Returns whether field is to be auto incremented ('AUTO_INCREMENT')
     * @return bool - Returns whether the type if auto increment or not
     */
    public function isAutoIncrement() {
        return $this->auto_increment;
    }

    public function getOptions() : array {
        return parent::getOptions() + ["auto_increment" => $this->auto_increment];
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
            ($this->isAutoIncrement() ? " AUTO_INCREMENT" : "") .
            ($this->hasDefault() ? " DEFAULT = '" . $this->getDefault() . "'" : "") .
            ($is_next ? ", " : ");");
    }
}