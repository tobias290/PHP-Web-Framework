<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 20:33
 */

namespace Framework\Serializer;

/**
 * Interface Serializable
    * Defines methods that must be created in a class that has data that can be serialized
 * @package Framework\Interfaces
 */
interface Serializable {
    /**
     * @param string | null $name - Name to group the data under
     * @return array - Returns the data as an array
     */
    public function asArray($name=null);

    /**
     * @param string | null $name - Name to group the data under
     * @return string - Returns the data as JSON
     */
    public function asJson($name=null);

    /**
     * @param string | null $name - Name to group the data under
     * @return string - Returns the data as XML
     */
    public function asXml($name=null);
}
