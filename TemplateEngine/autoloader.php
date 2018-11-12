<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 31/05/2017
 * Time: 10:19
 */

spl_autoload_register(function($namespace) {
    $class = getClass($namespace);


    if(file_exists("$class.php"))
        return require_once("$class.php");
    else
        throw new Exception("'$class.php' does not exist");
});

/**
 * Gets the class name that is required (also the file name)
 */
function getClass($class){
    $split_route = explode("\\", $class);
    return end($split_route);
}