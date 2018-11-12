<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 22/06/2017
 * Time: 16:19
 */

namespace Framework\Helpers;


/**
 * Trait Attributes
    * Trait holds all function relating to adding, removing attributes to a HTML tag
 * @package Framework\Traits
 */
trait Attributes {
    protected $attributes = [];

    /**
     * @return mixed
     */
    public function getAttributes() {
        return $this->attributes;
    }

    /**
     * @param string $key - Name of the attribute
     * @param string $value - Value of the attribute of key if the attribute is a single keyword (e.g. selected, checked)
     * @return null | string - Returns a string with '$key=$value' or $value
     */
    private function callback($key, $value) {
        if(is_int($key))
            return $value;
        elseif($key == "label")
            return null;
        else
            return "$key='$value'";

    }

    /**
     * @param array $attributes - List of attributes to add to HTML tag
     * @return string - Returns a string with all the attributes in a (attribute='value') form
     */
    protected function insertAttributes($attributes=[]) {
        if($attributes == [] and $this->attributes == [])
            return "";
        elseif($attributes == [])
            return implode(" ", array_map([$this, "callback"], array_keys($this->attributes), $this->attributes));
        else
            return implode(" ", array_map([$this, "callback"], array_keys($attributes), $attributes));
    }

    /**
     * Function adds multiple attributes at once
     * @param $attributes - List of attributes to add
     * @return $this - Returns object instance so it will still be displayed
     */
    final public function addAttributes($attributes) {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Add an attribute to the attributes list
     * @param string $name - Name of attribute
     * @param string $value - Value of attribute
     * @return $this - Returns object instance so it will still be displayed
     */
    final public function addAttribute($name, $value) {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * Removes a attribute from the attributes list
     * @param $name - Name of attribute to remove
     * @return $this - Returns object instance so it will still be displayed
     */
    final public function removeAttribute($name) {
        if(in_array($name, array_keys($this->attributes)))
            unset($this->attributes[$name]);
        return $this;
    }
}























