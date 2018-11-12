<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 18/02/2018
 * Time: 22:46
 */

namespace Framework\Exceptions;


// Thrown if the user try to create a file that already exists
class FileExistError extends \Exception {}