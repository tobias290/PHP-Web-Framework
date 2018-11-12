<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 21:30
 */

namespace Framework\Serializer;


use Framework\Exceptions\KeyNotFoundException;


final class JsonDeserializer implements Deserializable {
    /**
     * @var string - Raw data passed to the instance
     */
    private $data;

    /**
     * @var mixed - Deserialized data
     */
    private $deserialized_data;

    /**
     * JSONDeserializer constructor.
     * @param string $data - Raw data to be deserialized
     */
    public function __construct(string $data) {
        $this->data = $data;

        $this->deserialize();
    }

    /**
     * @param string $key - Name of key to retrieve
     * @return mixed - Returns the element or array
     * @throws KeyNotFoundException - Thrown if key does not exist in data
     */
    public function __get($key) {
        if(key_exists($key, $this->deserialized_data))
            return $this->deserialized_data[$key];
        else
            throw new KeyNotFoundException("$key was not found");
    }

    /**
     * @inheritdoc
     */
    public function getDerserializedData() {
        return $this->deserialized_data;
    }

    /**
     * Deserializes the data
     */
    private function deserialize() {
        $this->deserialized_data = json_decode($this->data, true);
    }
}
