<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 26/06/2017
 * Time: 16:58
 */

namespace Framework\Helpers;


use Framework\Exceptions\CannotUnsetError;
use Framework\Exceptions\KeyNotFoundException;

/**
 * Class ArrayObject
    * A class to represent an array as a object
 * @package Framework\Helpers
 */
final class ArrayObject implements \ArrayAccess, \Countable, \Iterator {
    /**
     * @var array
     *
     * Array to be treated as an object
     */
    private $array;

    /**
     * @var bool
     *
     * Determines whether the data can be set or not
     */
    private $can_set;

    /**
     * @var bool
     *
     * Determines whether the data can be unset or not
     */
    private $can_unset;

    /**
     * ArrayObject constructor.
     * @param array $array - Array to be treated as an object
     * @param bool $can_set - Determines whether the data can be set or not
     * @param bool $can_unset - Determines whether the data can be unset or not
     */
    public function __construct($array, $can_set=false, $can_unset=false) {
        $this->array = $array;
        $this->can_set = $can_set;
        $this->can_unset = $can_unset;
    }

    /**
     * @param string $key - Key to look up in array
     * @return ArrayObject | mixed
     *                              - If the found is as an array it returns a new instance of 'ArrayObject' with the new key
     *                              - Otherwise it returns the item found under $key
     * @throws KeyNotFoundException
     */
    public function __get($key) {
        $this->checkKeyExists($key);

        if(is_array($this->array[$key]))
            return new ArrayObject($this->array[$key], $this->can_set, $this->can_unset);
        else
            return $this->array[$key];
    }

    /**
     * @param string $key - Key name of data being set
     * @param mixed $value - Value to set under $key
     * @throws \Exception - Thrown if the user is not allowed to create a new element
     */
    public function __set($key, $value) {
        if(!$this->can_set) throw new \Exception("Object cannot be set");

        $this->array[$key] = $value;
    }

    /**
     * @param string $key - Key to find in the array to unset
     * @throws \Exception - Thrown if the user is not allowed to unset
     */
    public function __unset($key) {
        if(!$this->can_unset) throw new \Exception("Object cannot be unset");

        $this->checkKeyExists($key);

        unset($this->array[$key]);
    }

    // _____________________________________________ ArrayAccess _______________________________________________________

    /**
     * @inheritdoc
     */
    public function offsetGet($offset) {
        return $this->__get($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value) {
        $this->__set($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset) {
        throw new CannotUnsetError("Cannot unset data");
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset) {
        return isset($this->array[$offset]);
    }

    // ___________________________________________ End ArrayAccess _____________________________________________________

    // ______________________________________________ Countable ________________________________________________________

    /**
     * @inheritdoc
     */
    public function count() {
        return count($this->array);
    }

    // ___________________________________________ End  Countable ______________________________________________________

    // _____________________________________________ Iterator __________________________________________________________

    /**
     * @inheritdoc
     */
    public function current() {
        return current($this->array);
    }

    /**
     * @inheritdoc
     */
    public function key() {
        return key($this->array);
    }

    /**
     * @inheritdoc
     */
    public function next() {
        next($this->array);
    }

    /**
     * @inheritdoc
     */
    public function rewind() {
        reset($this->array);
    }

    /**
     * @inheritdoc
     */
    public function valid() {
        return key($this->array) !== null;
    }

    // ____________________________________________ End Iterator _______________________________________________________

    /**
     * @param string $key - Key to look up in the array
     * @throws KeyNotFoundException - Thrown if key was not found in the array
     */
    private function checkKeyExists($key) {
        if(!key_exists($key, $this->array))
            throw new KeyNotFoundException("$key does not exist");
    }
}