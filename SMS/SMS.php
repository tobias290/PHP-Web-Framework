<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 11/01/2018
 * Time: 19:48
 */

namespace Framework\SMS;

require_once "../Framework/vendor/autoload.php";

use Framework\Config;
use Nexmo\Client;
use Nexmo\Client\Credentials\Basic;

final class SMS {
    private static $client;

    private function __construct() {
        self::$client = new Client(new Basic(Config::instance()->sms->api_key, Config::instance()->sms->api_secret));
    }

    public static function send($to, $msg) {
        new SMS;

        self::$client->message()->send([
            "to" => $to,
            "from" => "PHP Framework",
            "text" => $msg,
        ]);
    }
}