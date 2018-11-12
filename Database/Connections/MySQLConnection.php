<?php

/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 29/06/2017
 * Time: 12:39
 */

namespace Framework\Database\Connections;


final class MySQLConnection implements ConnectionInterface {
    use Connection;

    /**
     * @inheritdoc
     */
    public function __construct($host, $user, $password, $database) {
        $this->dbc = mysqli_connect($host, $user, $password, $database) or die(mysqli_connect_error());
        mysqli_set_charset($this->dbc, "utf8");

        $this->is_connected = true;
    }

    /**
     * @inheritdoc
     */
    public function ping() {
        return mysqli_ping($this->dbc);
    }

    /**
     * @inheritdoc
     */
    public function error() {
        return mysqli_error($this->dbc);
    }

    /**
     * @inheritdoc
     */
    public function query($sql) {
        return mysqli_query($this->dbc, $sql);
    }

    /**
     * @inheritdoc
     */
    public function fetch_all($result, $result_type=ResultTypes::NUM) {
        return mysqli_fetch_all($result, $result_type);
    }

    /**
     * @inheritdoc
     */
    public function fetch_array($result, $result_type=ResultTypes::BOTH) {
        return mysqli_fetch_array($result, $result_type);
    }

    /**
     * @inheritdoc
     */
    public function fetch_assoc($result) {
        return mysqli_fetch_assoc($result);
    }

    /**
     * @inheritdoc
     */
    public function num_rows($result) {
        return mysqli_num_rows($result);
    }
}