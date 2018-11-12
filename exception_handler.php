<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 30/05/2017
 * Time: 11:13
 */

require_once "vendor/autoload.php";

use Framework\Http\Responses\ResponseBadRequest;


set_exception_handler("handler");

/**
 * @param Exception $e
 * @throws \Framework\Exceptions\UnexpectedStatusCode
 */
function handler($e) {
    $debug = parse_ini_file(__DIR__ . "/../config.ini", true)["app"]["debug"] ?? false;

    if($debug) {
        echo "<h1>Error: " . get_class($e) . "</h1>";
        echo "<hr>";
        echo "<h2>" . $e->getMessage() . "</h2>";
        echo "<hr>";
        echo "<h2>In:</h2> " . $e->getFile() . " <strong>on line " . $e->getLine() . "</strong>";
        echo "<hr>";
        echo "<h2>Stack Trace:</h2>";
        echo "<pre>";
        echo $e->getTraceAsString();
        echo "</pre>";
    } else {
        (new ResponseBadRequest())->send();
    }
}