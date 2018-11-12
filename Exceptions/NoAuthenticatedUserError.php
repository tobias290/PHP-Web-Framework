<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 18/02/2018
 * Time: 22:47
 */

namespace Framework\Exceptions;


// Thrown if a controller tries to log out a user when one hasn't been logged in
class NoAuthenticatedUserError extends \Exception {}