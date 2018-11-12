<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:49
 */

namespace Framework\Forms\Types;


final class SubmitInput extends AbstractInputType {
    protected $type = "submit";

    /**
     * TextInput constructor.
     * @param string $value - Text to display in input
     * @param array $attributes - Other attributes to define for this input
     */
    public function __construct($value, array $attributes = []) {
        $attributes["value"] = $value;
        parent::__construct($attributes);
    }
}