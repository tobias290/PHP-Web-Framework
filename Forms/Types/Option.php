<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:42
 */

namespace Framework\Forms\Types;


use Framework\Helpers\Attributes;

final class Option {
    use Attributes;

    private $name, $value, $selected;

    /**
     * Option constructor.
     * @param string $name - Name of option
     * @param string $value - Value of option
     * @param bool $selected - Whether this field is selected or not
     * @param array $attributes - Other attributes to add
     */
    public function __construct($name, $value, $selected=false, $attributes=[]) {
        $this->name = $name;
        $this->value = $value;
        $this->selected = $selected;
        $this->attributes = $attributes;
    }

    public function __toString() {
        if($this->selected)
            return "<option value='$this->value' selected " . implode(" ", array_map([$this, "callback"], array_keys($this->attributes), $this->attributes)) .">$this->name</option>";
        else
            return "<option value='$this->value' " . implode(" ", array_map([$this, "callback"], array_keys($this->attributes), $this->attributes)) . ">$this->name</option>";
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isSelected(): bool {
        return $this->selected;
    }
}