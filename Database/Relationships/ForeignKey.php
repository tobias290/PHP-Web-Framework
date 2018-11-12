<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:34
 */

namespace Framework\Database\Relationships;


use Framework\Database\Types\AbstractType;
use Framework\Database\Types\Integer;
use Framework\Forms\Types\TextInput;


/**
 * Class ForeignKey
 * Class to represent a foreign key
 * @package Framework\Database
 */
final class ForeignKey extends AbstractRelationship {
//    protected $form_class = Select::class;
    protected $table;
    protected $reference_column;

    /**
     * ForeignKey constructor.
     * @param string $reference_field - Column to reference in the $table
     * @inheritdoc
     */
    public function __construct($table, $reference_column="id") {
        parent::__construct($table);
        $this->table = $table;
        $this->reference_column = $reference_column;
    }


    /**
     * @inheritdoc
     */
    public function getSQL($name, $is_next) {
        if($is_next)
            return "FOREIGN KEY ($name) REFERENCES {$this->getTable()}($this->reference_column),";
        else
            return "FOREIGN KEY ($name) REFERENCES {$this->getTable()}($this->reference_column));";
    }
}