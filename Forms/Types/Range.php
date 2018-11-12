<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:48
 */

namespace Framework\Forms\Types;


final class Range extends AbstractInputType {
    protected $type = "range";

    public function __construct($min, $max, $step, $attributes = []) {
        $attributes["min"] = $min;
        $attributes["max"] = $max;
        $attributes["step"] = $step;
        parent::__construct($attributes);
    }
}