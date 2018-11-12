<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 17/06/2017
 * Time: 17:20
 */

namespace Framework\Helpers;

/**
 * Trait ArrayFunctions
 * Holds various reusable array functions
 * @package Framework\Traits
 */
trait ArrayFunctions {
    /**
     * Loops over an array and creates a new array of only the values with $key
     *
     * @param $key - Key to search for
     * @param $arr - Array to search for key in
     * @return array|mixed - Returns the new array
     */
    private function arrayValueRecursive($key, $arr){
        $val = array();
        array_walk_recursive($arr, function($v, $k) use($key, &$val){
            if($k == $key) array_push($val, $v);
        });
        return count($val) > 1 ? $val : array_pop($val);
    }
}