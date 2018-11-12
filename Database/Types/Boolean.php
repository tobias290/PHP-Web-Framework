<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:35
 */

namespace Framework\Database\Types;

use Framework\Forms\Types\Checkbox;

/**
 * Class Boolean
 * Represents the SQL type 'BOOLEAN'
 * @package Framework\Database
 */
final class Boolean extends AbstractType {
    protected $type_sql_value = "BOOLEAN";
    protected $form_class = Checkbox::class;

    /**
     * @inheritdoc
     */
    public function getDefault() {
        return $this->options["default"] ? "TRUE" : "FALSE";
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
            ($this->hasDefault() ? " DEFAULT " . $this->getDefault() : "") .
            ($is_next ? ", " : ");");
    }
}