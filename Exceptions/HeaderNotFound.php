<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 18/02/2018
 * Time: 22:48
 */

namespace Framework\Exceptions;


// Thrown if the user tried to get a header that doesn't exist
class HeaderNotFound extends \Exception {}