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
    * Class to send XML data as the content for the response
 * @package Framework\Responses
 */
class XmlResponse extends Response {
    public function __construct(array $content = null, array $headers = []) {
        $this->headers["Content-Type"] = "application/xml; charset=utf-8";

        parent::__construct(xmlrpc_encode($content), 200, $headers);
    }

    /**
     * Set content if it has already been turned into XML
     * This is because you cannot pass XML content to the constructor only an array
     *
     * @param string $json
     */
    public function setXmlContent(string $json) {
        $this->content = $json;
    }
}