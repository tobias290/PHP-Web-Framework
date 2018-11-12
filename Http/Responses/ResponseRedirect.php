<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 06/01/2018
 * Time: 23:02
 */

namespace Framework\Http\Responses;


class ResponseRedirect extends Response {
    public function __construct($content = null) {
        parent::__construct($content, 302);
    }
}