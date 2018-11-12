<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 18/02/2018
 * Time: 22:47
 */

namespace Framework\Exceptions;


// Thrown is the user try to create a user and tries to insert a property that isn't allowed (e.g. is_active)
class DetailNotAllowedException extends \Exception {}