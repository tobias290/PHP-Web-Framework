<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 18/02/2018
 * Time: 22:39
 */

namespace Framework\Exceptions;


// Thrown if a session user to tried to get a unset session variable
class SessionVariableNotFound extends \Exception {}