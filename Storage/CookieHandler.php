<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 13/01/2017
 * Time: 21:45
 */

namespace Framework\Storage;

use Framework\Exceptions\CookieVariableNotFound;
use Framework\Helpers\Value;

/**
 * Class Cookie
    * Class to represent a cookie ($_COOKIE)
 * @package Framework\Sessions
 */
final class CookieHandler implements \Countable {
    private $cookies;

    /**
     * Cookie constructor.
     *
     * @var $_COOKIE $cookie - Represents the $_COOKIE super global
     */
    public function __construct(){
        $this->cookies = &$_COOKIE;
    }

    /**
     * Returns all the cookies
     */
    public function getCookies() {
        return $this->cookies;
    }

    /**
     * Gets a cookie value from a name
     *
     * @param string $key - Name to look for cookie under
     * @return string | int | object - Returns the value under $name
     */
    public function getCookie($key){
        $value = $this->cookies[$key];

        if(empty($value))
            //throw new CookieVariableNotFound("Key not found in any cookies: $key");
            return null;
        else
            return $value;
    }

    /**
     * Sets a new cookie
     *
     * @param string $name - Name to store cookie under
     * @param string $value - Value to store
     * @param int $expire - The time the cookie will expire
     * @param null|string $path - The path of the server in which the cookie will be available from
     * @param string|null $domain - The (sub)domain that the cookie is available too
     * @param bool $secure - Indicates if the cookie should only be sent over HTTPS connection (if true then the cookie will only be send over a secure connection)
     * @param bool $http_only - Indicates whether the cookie is available only via the HTTP protocol (if ttrue then it will only be made accessible through the HTTP protocol)
     * @param bool $raw - Indicates whether the cookie should be set raw or not
     */
    public function setCookie(string $name, string $value = null, $expire = 0, string $path = '/', string $domain = null, bool $secure = false, bool $http_only = true, bool $raw = false){
        if($raw)
            setrawcookie($name, $value, $expire, $path, $domain, $secure, $http_only);
        else
            setcookie($name, $value, $expire, $path, $domain, $secure, $http_only);
    }

    /**
     * Un-sets a cookie
     *
     * @param string $key - Name to look for cookie under
     * @throws CookieVariableNotFound - Thrown is cookie under $name isn't found
     */
    public function unsetCookie($key){
        if(empty($this->cookies[$key])) throw new CookieVariableNotFound("Cannot unset empty cookie");

        unset($this->cookies[$key]);
        setcookie($key, '', time() - 3600, '/');
    }

    /**
     * Returns whether a cookie is set or not under $key
     *
     * @param string $key - Name to look for cookie under
     * @return bool - Returns true if cookie is set and false if it isn't
     */
    public function isCookieSet($key){
        return !empty($this->cookies[$key]);
    }

    // ______________________________________________ Countable ________________________________________________________

    /**
     * @inheritdoc
     */
    public function count() {
        return count($this->cookies);
    }

    // ___________________________________________ End  Countable ______________________________________________________
}