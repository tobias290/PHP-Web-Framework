<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 09/01/2018
 * Time: 21:12
 */

namespace Framework\Security;


use Framework\Exceptions\HashNotCreatedError;

/**
 * Class Hash
    * Wrapper and helper class for creating password hashes
 * @package Framework\Security
 */
final class Hash {
    private function __construct() {}

    /**
     * Method for creating a hash
     *
     * @param string $value - The value to hash
     * @param array $options - An associative array contains option for the hashing (e.g. cost)
     * @return string - Returns the user hash is created
     * @throws HashNotCreatedError - Thrown if it failed to create the hash
     */
    public static function make($value, $options=[]) {
        $hash = password_hash($value, PASSWORD_BCRYPT, $options);

        if ($hash == false)
            throw new HashNotCreatedError("PHP failed to create a hash");

        return $hash;
    }

    /**
     * Method for checking a value to a hash
     *
     * @param string $value - Value to check
     * @param string $hash - Hash to match against the value
     * @return bool - Either returns true if they match or false if they don't
     */
    public static function verify($value, $hash) {
        return password_verify($value, $hash);
    }
}