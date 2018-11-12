<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 18/02/2018
 * Time: 22:42
 */


namespace Framework\Exceptions;

// Thrown if the user tries to use a HTTP method in the 'ClassViewController' then the appropriate method has not been implemented
class MethodNotImplemented extends \Exception {}