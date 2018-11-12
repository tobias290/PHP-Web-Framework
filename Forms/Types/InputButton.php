<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:45
 */

namespace Framework\Forms\Types;


final class InputButton extends AbstractInputType {
    protected $type = "button";

    /**
     * Button constructor.
     * @param string $value - Value of the button (i.e. the text presented)
     * @param array $attributes
     */
    public function __construct($value, array $attributes = []) {
        $attributes["value"] = $value;
        parent::__construct($attributes);
    }
}