<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 14/05/2017
 * Time: 16:45
 */

namespace Framework\Views;
use Framework\Controllers\BaseController;
use Framework\Http\Responses\Response;
use Framework\Http\Routing\Router;


/**
 * Class APIView
    * Represents an API view that load either JSON or XML
 * @package Framework\views
 */
final class APIView {
    private $controller;
    private $router;
    private $route;

    /**
     * APIView constructor.
     * @param BaseController $controller - Represent the view controller this view is being used from
     * @param Router $router - Represents the router class
     * @param string $route - URL currently navigated to
     */
    public function __construct($controller, $router, $route){
        $this->controller = $controller;
        $this->router = $router;
        $this->route = $route;
    }

    /**
     * Loads the page on the website as either JSON or XML
     * @param array $vars - Variable as an array to encode as JSON or XML
     * @param string $as - Specifies whether the data is to be presented as JSON or XML
     * @return string - Returns the data in the correct format
     * @throws \Framework\Exceptions\UnexpectedStatusCode
     */
    public function load($vars, $as="json") {
        if (strtolower($as) == "xml") {
            $response = new Response(xmlrpc_encode($vars));
            $response->setHeader("Content-type", "application/xml");
            return $response;
        } else {
            $response = new Response(json_encode($vars));
            $response->setHeader("Content-type", "application/json");
            return $response;
        }
    }
}