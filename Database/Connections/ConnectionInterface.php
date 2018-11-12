<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 29/06/2017
 * Time: 12:32
 */

namespace Framework\Database\Connections;


interface ConnectionInterface {
    public function __construct($host, $user, $password, $database);

    public function dbc();

    public function isConnected();

    public function ping();

    public function error();

    public function query($sql);

    public function fetch_all($result, $result_type=ResultTypes::NUM);

    public function fetch_array($result, $result_type=ResultTypes::BOTH);

    public function fetch_assoc($result);

    public function num_rows($result);
}