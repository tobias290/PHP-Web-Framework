<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:42
 */

namespace Framework\Forms\Types;


use Framework\Helpers\Attributes;

final class OptionGroup {
    use Attributes;

    private $label, $options;

    /**
     * OptionGroup constructor.
     * @param string $label - Label for this option group
     * @param array $options - List of options ('Option' class)
     * @param array $attributes - Other attributes to add
     */
    public function __construct($label, $options, $attributes=[]) {
        $this->label = $label;
        $this->options = $options;
        $this->attributes = $attributes;
    }

    public function __toString() {
        $input = "<optgroup label='$this->label' " . implode(" ", array_map([$this, "callback"], array_keys($this->attributes), $this->attributes)) . ">";

        foreach ($this->options as $option) {
            $input .= "\t $option \n";
        }

        $input .= "</optgroup>";

        return $input;
    }
}