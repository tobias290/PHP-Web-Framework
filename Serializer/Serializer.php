<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 20:51
 */

namespace Framework\Serializer;

use Framework\Database\DataEntity;
use Framework\Database\Result;
use Framework\Exceptions\IncorrectDataType;

/**
 * Class Serializer
    * Class for serializing data in array form or as a 'Result' or 'DataEntity' class
    * Data can either be turned into array, JSON, XML
    * Using a serializer class also give the user options to hide certain columns
 * @package Framework\Serializer
 */
final class Serializer implements Serializable {
    /**
     * @var array - Data to be serialized
     */
    private $data;

    /**
     * @var array - List of columns to be hidden/removed from the data
     */
    private $hide;

    /**
     * Serializer constructor.
     * @param array | Result | DataEntity $data - Data to be serialized
     * @param array $hide - Specifies columns to hide in the data
     * @throws IncorrectDataType - Thrown if incorrect data type was passed
     */
    public function __construct($data, $hide=[]) {
        $this->hide = $hide;

        if(is_array($data))
            $this->data = $data;
        elseif(($data instanceof Result) or ($data instanceof DataEntity))
            $this->data = $data->asArray();
        else
            throw new IncorrectDataType("The data must either be array, 'Result' or 'DataEntiy' not " . get_class($data));

       $this->hideColumns();
    }

    /**
     * @inheritdoc
     */
    public function asArray($name=null) {
        if($name == null) {
            return $this->data;
        } else {
            return [$name => $this->data];
        }
    }

    /**
     * @inheritdoc
     */
    public function asJson($name=null) {
        if($name == null) {
            return json_encode($this->data);
        } else {
            return json_encode([$name => $this->data]);
        }
    }

    /**
     * @inheritdoc
     */
    public function asXml($name=null) {
        if($name == null) {
            return xmlrpc_encode($this->data);
        } else {
            return xmlrpc_encode([$name => $this->data]);
        }
    }

    /**
     * Loops over the data and removes all elements that are in the $hide property
     */
    private function hideColumns() {
        if($this->hide == []) return;

        foreach ($this->data as $key => $datum) {
            if(is_array($datum)) {
                $this->data[$key] = $this->innerLoop($datum);
            } elseif(in_array($key, $this->hide)) {
                unset($this->data[$key]);
            }
        }
    }

    /**
     * Does the same as 'hideColumns' except this is called for arrays inside another array
     * This is also called recursively for nested arrays
     * @param array $data - Data to loop over
     * @return mixed - Returns the data after removing elements that are in the $hide property
     */
    private function innerLoop($data) {
        foreach ($data as $key => $datum) {
            if(is_array($datum)) {
                $data[$key] = $this->innerLoop($datum);
            } elseif (in_array($key, $this->hide)) {
                unset($data[$key]);
            }
        }

        return $data;
    }
}

