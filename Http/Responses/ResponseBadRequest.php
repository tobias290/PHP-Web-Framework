<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 06/01/2018
 * Time: 23:19
 */

namespace Framework\Http\Responses;


class ResponseBadRequest extends Response {
    public function __construct($content = null) {
        parent::__construct($content, 400);
    }
}