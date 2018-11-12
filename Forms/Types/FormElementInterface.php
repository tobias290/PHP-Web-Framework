<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 23/06/2017
 * Time: 19:25
 */

namespace Framework\Forms\Types;

/**
 * Interface FormElementInterface
    * Defines methods that must be created in a class represents a HTML form element
 * @package Framework\Interfaces
 */
interface FormElementInterface {
    /**
     * Returns the element as a string in it's HTML form
     * @return string
     */
    public function __toString();

    /**
     * Gets the name of the form element
     * @return mixed
     */
    public function getName();

    /**
     * Sets the name of the form element
     * @param string $name - Name to set form element
     */
    public function setName($name);

    /**
     * Gets the value of the form element
     * @return mixed
     */
    public function getValue();

    /**
     * Sets the value of the form element
     * @param mixed $value - Value to set
     */
    public function setValue($value);

    /**
     * @return boolean - Returns whether each element is valid or not
     */
    public function isValid();
}