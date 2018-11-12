<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 26/06/2017
 * Time: 19:24
 */

namespace Framework\Serializer;

/**
 * Interface Deserializable
    * Defines methods that must be created in a class that is used as a deserializer
 * @package Framework\Interfaces
 */
interface Deserializable {
    /**
     * @param string $key - Key to get form deserialized data
     * @return mixed - Returns the data under $key
     */
    public function __get($key);

    /**
     * Deserializes the data
     */
    public function getDerserializedData();
}