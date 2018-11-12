<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 21/06/2017
 * Time: 12:41
 */

namespace Framework\Forms;

use Framework\Database\Types\AbstractType;
use Framework\Database\Relationships\ForeignKey;
use Framework\Database\Table;
use Framework\Forms\Types\{
    AbstractElement,
    AbstractInputType,
    Button,
    Output,
    SubmitInput
};
use Framework\Helpers\Attributes;
use Framework\Http\Requests\Request;


abstract class Form {
    use Attributes;

    /**
     * @var Table
     *
     * This attribute defines which table the form is for
     */
    protected $model = null;
    protected $is_model_used = false;

    /**
     * @var array
     *
     * If a model is used this array is used to say which fields/columns from the model to use
     */
    protected $fields = [];

    /**
     * Form constructor.
     * @param Request $request - The current request
     */
    public function __construct(Request $request) {
        if($this->model != null) {
            // Take the model class instantiate it and replace it
            $this->model = new $this->model();
            $this->is_model_used = true;
            $this->setModelFields();
        }

        $this->setNames();

        if($request->method == "POST")
            $this->populateFields($request);
    }

    /**
     * @param string $name - Name of form element to return
     * @return AbstractElement | AbstractInputType | mixed - Returns the form element as a string
     */
    final public function __get($name) {
        return $this->{$name};
    }

    /**
     * Returns the entire form if the form object was called as a method
     * @param string $action - Action pat
     * @param string $method - Method type (e.g. GET, POST)
     * @param array $other_attributes - Other attributes to add
     * @return string - Returns the top of the form
     */
    final public function __invoke($action, $method, $other_attributes=[]) {
        $form = $this->open($action, $method, $other_attributes) . "\n";

        foreach ($this->fields() as $field) {
            $form .= "\t $field <br><br> \n";
        }

        $form .= $this->close();

        return $form;
    }

    /**
     * @return array - Returns list of fields
     */
    final public function getFields() {
        return $this->fields;
    }

    /**
     * Sets the names of each field based on the property name
     */
    private function setNames() {
        $fields = $this->fields();

        foreach ($fields as $name => $object) {
            if($object instanceof AbstractElement or $object instanceof AbstractInputType)
                $this->{$name}->setName($name);
        }
    }

    /**
     * Sets each field if the user gave the form a model
     * If a model was given each field will be the element defined in each Database type's class
     */
    private function setModelFields() {
        if($this->fields == []) {
            $model_fields = $this->model->fields();
        } else {
            /*
             * First this takes the 'model_fields' and converts it to an associative array with the key being the value
             * This is so it the keys can be compared to the keys in the 'model' fields associative array
             * It then returns the objects in the models fields that keys are also in the 'model_field' array
             */
            $model_fields = array_intersect_key(
                $this->model->fields(),
                call_user_func_array(
                    'array_merge',
                    array_map(
                        function ($str) {return [$str => $str];},
                        $this->fields
                    )
                )
            );
        }

        /** @var ForeignKey | AbstractType $model_field */
        foreach ($model_fields as $name => $model_field) {
            // This is to allow the user to override fields already in the table
            // E.g. If the user want to override a password field so it's a password input not text input
            if(in_array($name, array_keys(get_object_vars($this)))) continue;

            $form_class = $model_field->getFormClass();
            $this->{$name} = new $form_class(["label" => ucfirst($name) . ": "]);
        }
    }

    /**
     * @param Request $request - Current request
     */
    private function populateFields($request) {
        $fields = $this->fields();

        foreach ($request->post() as $key => $value) {
            if(in_array($key, array_keys($fields))) {
                if($this->{$key} instanceof AbstractElement or $this->{$key} instanceof AbstractInputType) {
                    $this->{$key}->setValue($value);
                }
            }
        }
    }

    /**
     * This orders the fields in the way that the user has requested in the $fields property
     * @param $fields - Fields to order
     * @return array - Returns the fields in the correct order
     */
    private function getCorrectOrder($fields) {
        if($this->fields == []) return $fields;

        $ordered_fields = [];

        foreach ($this->fields as $field) {
            $ordered_fields[$field] = $fields[$field];
        }

        return $ordered_fields;
    }

    /**
     * @param string $action - Action pat
     * @param string $method - Method type (e.g. GET, POST)
     * @param array $other_attributes - Other attributes to add
     * @return string - Returns the top of the form
     */
    final public function open($action, $method, $other_attributes=[]) {
        // Returns the top of the form after inserting its action, method and converting an associative array to a string of $key = '$value'
        if($other_attributes != [])
            return "<form action='$action' method='$method' " . $this->insertAttributes($other_attributes) . ">";
        else
            return "<form action='$action' method='$method'>";
    }

    /**
     * Returns a form closing tag
     */
    final public function close() {
        return "</form>";
    }

    /**
     * Returns a list of all of the classes instance attributes
     */
    final public function fields() {
        $fields = get_object_vars($this);

        // Loops over the elements an un-sets it if it not a form element or it is not in the requested fields
        // Then it adds the model fields elements to the return list first so it appears in the correct order
        foreach ($fields as $name => $object) {
            if(
                !($object instanceof AbstractElement) and
                !($object instanceof AbstractInputType) and
                !($object instanceof Button) and
                !($object instanceof Output) or
                ($this->fields != [] and !in_array($name , $this->fields))
            ) {
                unset($fields[$name]);
            }
        }

        return $this->getCorrectOrder($fields);
    }

    /**
     * @return array - Returns a key-value list of all of the data
     */
    final public function getData() {
        $data = [];
        $fields = $this->fields();

        /** @var AbstractElement | AbstractInputType $field */
        foreach ($fields as $key => $field) {
            if(!($field instanceof SubmitInput) and !($field instanceof Button))
                $data[$key] = $field->getValue();
        }

        return $data;
    }

    /**
     * Checks whether each field is valid and if they all are then the form is valid
     * @return bool - Returns whether the form is valid or not
     */
    final public function isValid() {
        $fields = $this->fields();

        /** @var AbstractElement | AbstractInputType $field */
        foreach ($fields as $key => $field) {
            if(!$field->isValid()) {
                return false;
            }
        }
        return true;
    }
}