<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 18/02/2018
 * Time: 22:47
 */

namespace Framework\Exceptions;


// Thrown if the username or password is blank while creating a new auth user
class UserCreationError extends \Exception {}
