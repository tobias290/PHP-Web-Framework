<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 13/01/2017
 * Time: 21:13
 */

namespace Framework\Storage;

use Framework\Exceptions\SessionVariableNotFound;
use Framework\Helpers\Value;
use Framework\Security\Auth\AuthUser;

/**
 * Class Session
    * Represents a session ($_SESSION)
 * @package Framework\Sessions
 */
final class SessionHandler implements \ArrayAccess, \Countable {
    //private static $initialized = false;
    private $session;

    /**
     * Session constructor.
     * @var $_SESSION $session - Represents the $_SESSION super global
     */
    public function __construct(){
        @session_start();
        $this->session = &$_SESSION;
    }

    /**
     * Destroys the current session
     */
    public function destroySession(){
        @session_unset();
        @session_destroy();
    }

    /**
     * Shortcut for 'get()' function
     * @param string $name - Name to look up in session
     * @return value - Returns the value as this class
     */
    public function __get($name){
        return $this->get($name);
    }

    // _____________________________________________ ArrayAccess _______________________________________________________

    /**
     * @inheritdoc
     */
    public function offsetGet($offset) {
        return $this->get($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value) {
        $this->set($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset) {
        $this->unset($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset) {
        return isset($this->session[$offset]);
    }

    // ___________________________________________ End ArrayAccess _____________________________________________________

    // ______________________________________________ Countable ________________________________________________________

    /**
     * @inheritdoc
     */
    public function count() {
        return count($this->session);
    }

    // ___________________________________________ End  Countable ______________________________________________________

    /**
     * Gets a session value and returns it as a SessionValue class
     * @param string $key - Name to look up in session
     * @return Value - Returns the value as this class
     */
    public function get($key){
        if(!empty($this->session[$key])) $value = $this->session[$key];

        if(empty($value))
            //throw new SessionVariableNotFound("Key not found in session: $key");
            return new Value(null);
            //return null;
        else {
            /** @var Value $value */
            return $value;
        }
    }

    /**
     * Sets a session value
     * @param string $key - Name to save value under
     * @param $value - Value to save
     */
    public function set($key, $value){
        $this->session[$key] = new Value($value);
        //$this->session[$key] = $value;
    }

    /**
     * Un-sets a session value
     * @param string $key - Name of session value to unset
     * @throws SessionVariableNotFound - Thrown if variable is not found under the name $key
     */
    public function unset($key){
        if(empty($this->session[$key])) throw new SessionVariableNotFound("Cannot unset empty session variable");

        unset($this->session[$key]);
    }

    /**
     * Returns the authenticated user
     * @return AuthUser
     */
    public function getAuthUser() {
        return $this->session["auth_user"] ?? null;
    }

    /**
     * Sets a session value
     * @param AuthUser $user - Value to save
     */
    public function setAuthUser($user){
        $this->session["auth_user"] = $user;
    }

    /**
     * Returns true is session is already started
     */
    public function isSessionStarted() {
        return session_status() == PHP_SESSION_ACTIVE;
    }

    /**
     * Returns true is session is disabled
     */
    public function isSessionDisabled() {
        return session_status() == PHP_SESSION_DISABLED ;
    }

    /**
     * Returns true is session is none
     */
    public function isSessionNone() {
        return session_status() == PHP_SESSION_NONE;
    }
}