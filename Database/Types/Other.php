<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:38
 */

namespace Framework\Database\Types;

use Framework\Forms\Types\{Button, AbstractElement, AbstractInputType};


/**
 * Class Other
 * Allows the user to specify a more specific data type that is not defined as a Type subclass object
 * @package Framework\Database
 */
final class Other extends AbstractType {
    /**
     * Other constructor.
     * @param string $data_type_string - String representation of SQL data type
     * @param AbstractElement | Button | AbstractInputType $form_class - Class to represent this type in a form
     * @param array $options - List of options for the column
     */
    public function __construct($data_type_string, $form_class, $options=[]) {
        $this->type_sql_value = $data_type_string;
        $this->form_class = $form_class;
        parent::__construct($options);
    }
}