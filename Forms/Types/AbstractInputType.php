<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:44
 */

namespace Framework\Forms\Types;


use Framework\Helpers\Attributes;

abstract class AbstractInputType implements FormElementInterface {
    use Attributes;

    /**
     * @var string
     *
     * Represents the input's type
     * E.g. <input type='$type'>
     */
    protected $type;

    /**
     * @var string
     *
     * Represents the input's name
     * E.g. <input name='$name'>
     */
    private $name;

    /**
     * @var mixed
     *
     * Represents the value for the field not be confused with the 'value' attribute
     */
    private $value;

    public function __construct($attributes=[]) {
        $this->attributes = $attributes;
    }

    /**
     * @return string - Returns the input as a string
     */
    public function __toString() {
        if(in_array("label", array_keys($this->attributes))) {
            // Returns the input after inserting its name, type and converting an associative array to a string of $key = '$value'

            // Splits the label if it contains an underscore an replaces it with a space then capitalises the first letter of each word
            // Or if it doesn't have a underscore just capitalise the first letter
            $label = strpos($this->attributes["label"], "_") !== false ? ucwords(str_replace("_", " ", $this->attributes["label"])) : ucfirst($this->attributes["label"]);

            return "<label>" . $label . "</label><input name='" . $this->name . "' type='" . $this->type . "' " . $this->insertAttributes() . ">";
        } else {
            // Returns the input after inserting its name, type and converting an associative array to a string of $key = '$value'
            return "<input name='" . $this->name . "' type='" . $this->type . "' " . $this->insertAttributes() . ">";
        }
    }

    /**
     * @inheritdoc
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @inheritdoc
     */
    public function isValid() {
        $required = $this->attributes["required"] ?? false;
        $pattern = $this->attributes["pattern"] ?? false;

        if($required and !empty($this->value)) {
            $valid_required = true;
        } elseif(!$required) {
            $valid_required = true;
        } else {
            $valid_required = false;
        }

        if($pattern) {
            if(preg_match("/$pattern/", $this->value)) {
                $valid_regex = true;
            } else {
                $valid_regex = false;
            }
        } else {
            $valid_regex = true;
        }

        return $valid_required and $valid_regex;
    }
}