<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:43
 */

namespace Framework\Forms\Types;


use Framework\Helpers\Attributes;

final class Button {
    use Attributes;

    private $type, $value;

    /**
     * Button constructor.
     * @param string $type - Type of button
     * @param string $value - Value of button (aka - the text)
     * @param array $attributes - Other attributes to give this element
     */
    public function __construct($type, $value, $attributes=[]) {
        $this->type = $type;
        $this->value = $value;
        $this->attributes = $attributes;
    }

    public function __toString() {
        return "<button type='$this->type' " . $this->insertAttributes() . ">$this->value</button>";
    }
}