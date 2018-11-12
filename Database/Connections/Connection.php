<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 29/06/2017
 * Time: 12:42
 */

namespace Framework\Database\Connections;

/**
 * Trait Connection
    * Common set of functions for a class that is related to connecting to a Database engine
 * @package Framework\Traits
 */
trait Connection {
    private $dbc;
    private $is_connected = false;

    /**
     * Returns Database connection instance
     */
    public function dbc() {
        return $this->dbc;
    }

    /**
     * @return boolean
     */
    public function isConnected() {
        return $this->is_connected;
    }
}