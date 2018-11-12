<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 18/02/2018
 * Time: 22:47
 */

namespace Framework\Exceptions;


// Thrown if the user tried to unset something when they are not allowed to
class CannotUnsetError extends \Exception {}