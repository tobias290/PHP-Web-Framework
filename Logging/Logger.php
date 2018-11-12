<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 05/06/2017
 * Time: 20:47
 */

namespace Framework\Logging;


use Framework\Config;

final class Logger {
    /** Stops instantiation of the class */
    private function __construct() {}

    private static function write($level, $message) {
        $destination = Config::instance()->log->log_path;
        $file_name = Config::instance()->log->log_file_name;

        $log = fopen("../$destination/$file_name.log", "a");

        if(!empty(Config::instance()->log->date_format))
            $date = date(Config::instance()->log->date_format);
        else
            $date = date("h:i:s d-m-Y");

        fwrite($log, "[$date] ". strtoupper($level) . ": $message\n");
    }

    public static function debug($message) {
        self::write(__FUNCTION__, $message);
    }

    public static function info($message) {
        self::write(__FUNCTION__, $message);
    }

    public static function notice($message) {
        self::write(__FUNCTION__, $message);
    }

    public static function warning($message) {
        self::write(__FUNCTION__, $message);
    }

    public static function error($message) {
        self::write(__FUNCTION__, $message);
    }

    public static function critical($message) {
        self::write(__FUNCTION__, $message);
    }

    public static function alert($message) {
        self::write(__FUNCTION__, $message);
    }

    public static function emergency($message) {
        self::write(__FUNCTION__, $message);
    }

    public static function migration($message) {
        $destination = Config::instance()->log->migration_path;

        $log = fopen("../$destination/migration.log", "a");

        if(!empty(Config::instance()->log->date_format))
            $date = date(Config::instance()->log->date_format);
        else
            $date = date("h:i:s d-m-Y");

        fwrite($log, "[$date] ". strtoupper(__FUNCTION__) . ": $message\n");
    }
}

