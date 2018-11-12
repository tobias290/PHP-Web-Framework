<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 01/06/2017
 * Time: 13:27
 */

namespace Framework;

use Framework\Exceptions\SectionNotDefined;
use Framework\Helpers\ArrayObject;

/**
 * Class Config
    * This is a singleton class to access the users 'config.ini' file
 * @package Framework
 */
final class Config {
    private $ini_file;
    private static $instance;

    /** Stops instantiation of the class outside the object */
    private function __construct() {
        $this->ini_file = parse_ini_file(__DIR__. "../../config.ini", true);
    }

    /**
     * Returns the class instance or creates one if it doesn't exist
     */
    public static function instance() {
        if(self::$instance === null)
            self::$instance = new Config();
        return self::$instance;
    }

    /**
     * @param string $name - Name of section to retrieve from the 'config.ini' file
     * @return ArrayObject - Returns an 'ArrayObject' which will be a list of all the settings under the given section
     * @throws SectionNotDefined - Thrown if the user tries to get a section that doesn't exist in the 'config.ini' file
     */
    public function __get($name) {
        if(key_exists($name, $this->ini_file))
            return new ArrayObject($this->ini_file[$name]);
        else
            throw new SectionNotDefined("$name is not defined in config.ini");
    }
}