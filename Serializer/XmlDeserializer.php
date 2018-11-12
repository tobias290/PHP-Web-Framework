<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 26/06/2017
 * Time: 19:10
 */

namespace Framework\Serializer;


use Framework\Exceptions\KeyNotFoundException;
use SimpleXMLElement;

final class XmlDeserializer implements Deserializable {
    /**
     * @var string - Raw data passed to the instance
     */
    private $data;

    /**
     * @var mixed - Deserialized data
     */
    private $deserialized_data;

    /**
     * @var bool - Determines whether the data is a raw string or a route to a file
     */
    private $from_file;

    /**
     * XMLDeserializer constructor.
     * @param string $data - Raw data to be deserialized
     * @param bool $from_file - Determines whether the data is a raw string or a route to a file
     */
    public function __construct(string $data, $from_file=false) {
        $this->data = $data;
        $this->from_file = $from_file;

        $this->deserialize();
    }

    /**
     * @param string $key - Key to look for in deserialized data
     * @return mixed - Returns the array/object/element found under $key
     * @throws KeyNotFoundException - Thrown if $key does not exist in deserialized data
     */
    public function __get($key) {
        if(key_exists($key, $this->deserialized_data))
            return $this->deserialized_data->{$key};
        else
            throw new KeyNotFoundException("$key was not found");
    }

    /**
     * @param string $name - Name of method to call from 'SimpleXMLElement' instance
     * @param $arguments - Arguments to pass to method
     * @return mixed - Returns the method
     */
    public function __call($name, $arguments) {
        if($arguments != null)
            return $this->deserialized_data->{$name}($arguments);
        else
            return $this->deserialized_data->{$name}();
    }

    /**
     * @return SimpleXMLElement - Returns the deserialized data as a whole
     */
    public function getDerserializedData() {
        return $this->deserialized_data;
    }

    /**
     * Deserializes the data
     */
    private function deserialize() {
        if($this->from_file)
            $this->deserialized_data = simplexml_load_file($this->data);
        else
            $this->deserialized_data = simplexml_load_string($this->data);
    }
}
