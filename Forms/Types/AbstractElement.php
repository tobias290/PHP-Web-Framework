<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:41
 */

namespace Framework\Forms\Types;

use Framework\Helpers\Attributes;

abstract class AbstractElement implements FormElementInterface {
    use Attributes;

    /**
     * @var string
     *
     * Represents the elements's name
     */
    protected $name;

    /**
     * @var mixed
     *
     * Represents the value for the field not be confused with the 'value' attribute
     */
    protected $value;

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
    public function getValue() {
        return $this->value;
    }

    /**
     * @@inheritdoc
     */
    public function setValue($value) {
        $this->value = $value;
    }

    /**
     * @inheritdoc
     */
    public function isValid() {
        $required = $this->attributes["required"] ?? false;

        if($required and !empty($this->value)) {
            $valid = true;
        } elseif(!$required) {
            $valid = true;
        } else {
            $valid = false;
        }

        return $valid;
    }
}