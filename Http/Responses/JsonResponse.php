<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 17/01/2018
 * Time: 15:27
 */

namespace Framework\Http\Responses;

/**
 * Class JsonResponse
    * Class to send JSON data as the content for the response
 * @package Framework\Responses
 */
class JsonResponse extends Response {
    public function __construct(array $content = null, array $headers = []) {
        $this->headers["Content-Type"] = "application/json; charset=utf-8";
        parent::__construct(json_encode($content), 200, $headers);
    }

    /**
     * Set content if it has already been turned into JSON
     * This is because you cannot pass JSON content to the constructor only an array
     *
     * @param string $json
     */
    public function setJsonContent(string $json) {
        $this->content = $json;
    }
}