<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 18/02/2018
 * Time: 22:41
 */

namespace Framework\Exceptions;

// Thrown if the user tries to retrieve a undefined column
class ColumnNotFound extends \Exception {}