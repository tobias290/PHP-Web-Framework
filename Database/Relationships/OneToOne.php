<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 26/06/2017
 * Time: 20:52
 */

namespace Framework\Database\Relationships;


use Framework\Database\Types\Integer;
use Framework\Forms\Types\TextInput;

/**
 * Class OneToOne
    * Allows the user to create a one to one relationship
 * @package Framework\Database\Relationships
 */
final class OneToOne extends AbstractRelationship {
    protected $form_class = TextInput::class;
    protected $reference_column;

    public function __construct($table, $reference_column="id") {
        parent::__construct($table);
        $this->reference_column = $reference_column;
        $this->type = new Integer(["not_null" => true, "unique" => true]);
    }

    public function getSQL($name, $is_next) {
        if($is_next)
            return "FOREIGN KEY ($name) REFERENCES {$this->getTable()}($this->reference_column),";
        else
            return "FOREIGN KEY ($name) REFERENCES {$this->getTable()}($this->reference_column));";
    }
}