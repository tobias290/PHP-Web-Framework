<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 26/06/2017
 * Time: 16:56
 */

namespace Framework\Helpers;

/**
 * Class Value
 * Class to represent a value
 * @package Framework\Values
 */
final class Value {
    private $val;

    /**
     * Value constructor.
     * @param $val - Value to represent
     */
    public function __construct($val){
        $this->val = $val;
    }

    /**
     * @return mixed - Returns the value as a string representation of the class
     */
    public function __toString(){
        return (string)$this->val;
    }

    /**
     * Returns a boolean to sea whether the value is empty or not
     */
    public function isEmpty(){
        return empty($this->val);
    }

    /**
     * Returns a boolean to sea whether the value is set or not
     */
    public function isSet(){
        return isset($this->val);
    }
}