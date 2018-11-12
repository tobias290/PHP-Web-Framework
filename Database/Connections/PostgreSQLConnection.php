<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 29/06/2017
 * Time: 12:56
 */

namespace Framework\Database\Connections;



final class PostgreSQLConnection implements ConnectionInterface {
    use Connection;

    /**
     * @inheritdoc
     */
    public function __construct($host, $user, $password, $database) {
        $this->dbc = pg_connect("host=$host dbname=$database user=$user password=$password");
        $this->is_connected = true;
    }

    /**
     * @inheritdoc
     */
    public function ping() {
        return pg_ping($this->dbc);
    }

    /**
     * @inheritdoc
     */
    public function error() {
        return pg_last_error($this->dbc);
    }

    /**
     * @inheritdoc
     */
    public function query($sql) {
        return pg_query($this->dbc, $sql);
    }

    /**
     * @inheritdoc
     */
    public function fetch_all($result, $result_type=ResultTypes::NUM) {
        return pg_fetch_all($result);
    }

    /**
     * @inheritdoc
     */
    public function fetch_array($result, $result_type=ResultTypes::BOTH) {
        return pg_fetch_array($result, $result_type);
    }

    /**
     * @inheritdoc
     */
    public function fetch_assoc($result) {
        return pg_fetch_assoc($result);
    }

    /**
     * @inheritdoc
     */
    public function num_rows($result) {
        return pg_num_rows($result);
    }
}