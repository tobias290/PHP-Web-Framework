<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 18/02/2018
 * Time: 22:42
 */

namespace Framework\Exceptions;


// Thrown if the user try to end a block that wasn't started
class NoBlockStarted extends \Exception {}