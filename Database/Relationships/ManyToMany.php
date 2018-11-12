<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 26/06/2017
 * Time: 20:47
 */

namespace Framework\Database\Relationships;

use Framework\Database\DB;
use Framework\Forms\Types\TextInput;

/**
 * Class ManyToMany
    * This allows the user to make 'Many to Many' relationships
 * @package Framework\Database\Relationships
 */
final class ManyToMany extends AbstractRelationship {
    protected $form_class = TextInput::class;

    /**
     * @var string
     *
     * Name of the table class the column was defined in
     */
    protected $self;

    /**
     * @var string
     *
     * Name of table to join the two tables
     */
    protected $joining_table_name;

    /**
     * ManyToMany constructor.
     * @param string $self - Name of the table class the column was defined in
     * @param string $table - Name of table class this column references
     * @param string | null $joining_table_name - Name of table to join the two tables (if null then a name is automatically given)
     */
    public function __construct($self, $table, $joining_table_name=null) {
        parent::__construct($table);
        $this->self = call_user_func($self . '::getName');

        if(!empty($joining_table_name))
            $this->joining_table_name = $joining_table_name;
        else
            $this->joining_table_name = $this->self . "_" . $this->table;

        // Add joining table to the table instance joining tables array
        DB::addReferenceTable($joining_table_name);
    }

    /**
     * @return string
     */
    public function getJoiningTableName() {
        return $this->joining_table_name;
    }

    /**
     * Creates the joining table to create the many to many relationship between the 2 tables
     * @param string $name - @not_needed
     * @param bool $is_next - @not_needed
     * @return string
     */
    public function getSQL($name, $is_next) {
        $sql = "CREATE TABLE IF NOT EXISTS $this->joining_table_name (";
        $sql .= ($this->self."_id") . " INT NOT NULL, ";
        $sql .= ($this->table."_id") . " INT NOT NULL, ";
        $sql .= "PRIMARY KEY (" . ($this->self."_id") . ", ". ($this->table."_id") . "), ";
        $sql .= "FOREIGN KEY (" . ($this->self."_id") . ") REFERENCES $this->self(id), ";
        $sql .= "FOREIGN KEY (" . ($this->table."_id") . ") REFERENCES $this->table(id) ";
        $sql .= ");";

        return $sql;
    }
}