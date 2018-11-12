<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 18/02/2018
 * Time: 22:43
 */

namespace Framework\Exceptions;

// Thrown if the user tries to create a Mail object or send Mail when an encryption method was not specified
class MailMustBeEncryptedError extends \Exception {}