<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 26/06/2017
 * Time: 20:48
 */

namespace Framework\Database\Relationships;


use Framework\Database\Types\{DatabaseTypeInterface, Integer};
use Framework\Forms\Types\TextInput;

abstract class AbstractRelationship implements DatabaseTypeInterface {
    protected $form_class = TextInput::class;
    protected $type;
    protected $table;

    /**
     * AbstractRelationship constructor.
     * @param string $table - Table relationship references
     * @arg AbstractType $type - Data type of column
     */
    public function __construct($table) {
        $this->table = call_user_func($table .'::getName');
        $this->type = new Integer(["not_null" => true]);
    }

    /**
     * Returns the table the foreign key refers to
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * @inheritdoc
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @inheritdoc
     */
    public function getFormClass(): string {
        return $this->form_class;
    }
}