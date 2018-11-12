<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:43
 */

namespace Framework\Forms\Types;


use Framework\Helpers\Attributes;

final class Output {
    use Attributes;

    private $name, $for, $form;

    /**
     * Output constructor.
     * @param string $name - Name of output
     * @param string $for - Name of which other element(s) the output is for
     * @param string | null $form - Specifies one or more Forms the output element belongs to
     * @param array $attributes - Other attributes to give this element
     */
    public function __construct($name, $for, $form=null, $attributes=[]) {
        $this->name = $name;
        $this->for = $for;
        $this->form = $form;
        $this->attributes = $attributes;
    }

    public function __toString() {
        return "<output name='$this->name' for='$this->for' " . ($this->form != null ? "form='$this->form' " : " ") . $this->insertAttributes() . " ></output>";
    }
}