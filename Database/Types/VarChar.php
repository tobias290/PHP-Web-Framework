<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:36
 */

namespace Framework\Database\Types;

use Framework\Forms\Types\TextInput;


/**
 * Class VarChar
 * Represents the SQL type 'VARCHAR'
 * @package Framework\Database
 */
final class VarChar extends AbstractType {
    protected $type_sql_value = "VARCHAR";
    protected $form_class = TextInput::class;
    protected $length;

    /**
     * VarChar constructor.
     * @param array $options - List of options for the column
     */
    public function __construct($options=[]) {
        parent::__construct($options);

        if(array_key_exists("length", $options))
            $this->length = $options["length"];
        else
            $this->length = 20;
    }

    /**
     * Creates the type adding it length as it can vary
     * @return string - Returns the type with the given length
     */
    public function getType() {
        return $this->type_sql_value . "(" . $this->length .")";
    }

    /**
     * @return array - Returns the all the options at once
     */
    public function getOptions(): array {
        return $this->options + ["type" => $this->getType(), "length" => $this->length];
    }
}