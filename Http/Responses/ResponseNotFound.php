<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 06/01/2018
 * Time: 00:44
 */

namespace Framework\Http\Responses;


class ResponseNotFound extends Response {
    public function __construct($content = null, string $content_type = "text/html") {
        parent::__construct($content, 404, ["content_type" => $content_type]);
    }
}