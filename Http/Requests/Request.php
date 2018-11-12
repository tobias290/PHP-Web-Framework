<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 11/01/2017
 * Time: 18:20
 */

namespace Framework\Http\Requests;

use Framework\Exceptions\HeaderNotFound;
use Framework\Http\Routing\Route;
use Framework\Security\Auth\AuthUser;
use Framework\Storage\{SessionHandler, CookieHandler};
use Framework\Helpers\Value;

/**
 * Class Request
    * A class to represent a request (such as 'GET', 'POST')
    * Also include super globals like $_SERVER, $_FILES
 * @package Framework\Requests
 */
final class Request {
    /**
     * @var CookieHandler
     *
     * Used to get cookies requested by the user
     */
    private $cookie_handler;

    /**
     * @var array
     *
     * List of all the headers for this request
     */
    private $headers;

    /**
     * @var array
     *
     * Represents the $_GET super global
     */
    private $get;

    /**
     * @var array
     *
     * Represents the $_POST super global
     */
    private $post;

    /**
     * @var array
     *
     * Represents the $_FILES super global
     */
    private $files;

    /**
     * @var array
     *
     * Represents the $_SERVER super global
     */
    private $server;

    /**
     * @var Route
     *
     * Represents the current route in which this request was created
     */
    private $route;

    /**
     * @var string
     *
     * The method for this request
     */
    public $method;

    /**
     * @var array
     *
     * Parameters in the path
     */
    public $params;

    /**
     * @var array
     *
     * URL string queries
     */
    public $query_strings;

    /**
     * @var SessionHandler
     *
     * Represents the $_SESSION super global
     */
    public $session;

    /**
     * @var AuthUser | null
     *
     * Represents the authenticated user
     */
    public $user = null;

    /**
     * Request constructor.
     *
     * @param Route $route - The route for this request
     * @param string $method - The method for this request
     * @param array $params - Parameters in the path
     * @param array $query_strings - URL string queries
     */
    public function __construct($route, $method, $params, $query_strings) {
        $this->route = $route;
        $this->method = $method;
        $this->params = $params;
        $this->query_strings = $query_strings;
        $this->get = $_GET;
        $this->post = $_POST;
        $this->files = $_FILES;
        $this->server = $_SERVER;
        $this->session = new SessionHandler();
        $this->headers = getallheaders();

        $this->cookie_handler = new CookieHandler();

        if(!empty($this->session->getAuthUser()))
            $this->user = $this->session->getAuthUser();
    }

    /**
     * Returns either the $_GET super global or a certain value in $_GET
     *
     * @param string | null $key - Name of value to look for in $_GET
     * @return Value | array - Returns the value
     */
    final public function get($key=null){
        if($key != null)
            return new Value($this->get[$key]);
        else
            return $this->get;
    }

    /**
     * Returns either the $_POST super global or a certain value in $_POST
     *
     * @param string | null $key - Name of value to look for in $_POST
     * @return Value | array - Returns the value
     */
    final public function post($key=null){
        if($key != null)
            return new Value($this->post[$key]);
        else
            return $this->post;
    }

    /**
     * Returns a value in the $_FILES super global
     *
     * @param string $key - Name of value to look for in $_FILES
     * @return Value - Returns the value
     */
    final public function files($key){
        return new Value($this->files[$key]);
    }

    /**
     * Returns a value in the $_SERVER super global
     *
     * @param $key - Name of value to look for in $_SERVER
     * @return mixed - Returns the value
     */
    final public function server($key){
        return $this->server[$key];
    }

    /**
     * @return Route - Returns the route for this request
     */
    final public function route() {
        return $this->route;
    }

    /**
     * Returns a header value from the given header name
     *
     * @param string $header - Name of header to retrieve
     * @return mixed - Returns the value of the header
     * @throws HeaderNotFound - Thrown if the header was not found
     */
    final public function getHeader($header) {
        if(key_exists($header, $this->headers))
            return $this->headers[$header];
        else
            throw new HeaderNotFound("$header header was not found");
    }

    /**
     * Gets a cookie from a given name or thrown an error if it is not found
     *
     * @param string $name - Name of the cookie to find
     * @return Value|int|object|string
     */
    final public function getCookie(string $name) {
        return $this->cookie_handler->getCookie($name);
    }
}