<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:42
 */

namespace Framework\Forms\Types;


final class Select extends AbstractElement {
    private $options;

    /**
     * Select constructor.
     * @param array $options - List of options ('Option' class) or a list containing ('OptionGroup' class) with contains a list of ('Option' class)
     * @param array $attributes - List of attributes to add to the element
     */
    public function __construct($options, $attributes=[]) {
        $this->options = $options;
        $this->attributes = $attributes;
    }

    public function __toString() {
        $input = "";

        if(key_exists("label", $this->attributes)) $input .= "<label>{$this->attributes['label']}</label>";

        $input .= "<select name='$this->name' " . implode(" ", array_map([$this, "callback"], array_keys($this->attributes), $this->attributes)) . ">\n";

        foreach ($this->options as $option) {
            $input .= "\t $option \n";
        }

        $input .= "</select>";

        return $input;
    }
}