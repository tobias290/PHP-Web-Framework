<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 15/02/2018
 * Time: 20:24
 */

namespace Framework\Http\Routing;

// TODO: change to http classes
use Framework\Controllers\{
    AnnotationController, APIController, BaseController, ClassController, Controller
};
use Framework\Exceptions\KeyNotFoundException;
use Framework\Exceptions\MethodNotImplemented;
use Framework\Http\Middleware\MiddlewareQueue;
use Framework\Http\Requests\Request;
use Framework\Http\Responses\Response;
use Framework\Security\Auth\AuthUser;
use Framework\Views\{ActionView, APIView, View};

/**
 * Class RouteBuilder
    * Once a route has been found and matched this will build the route and execute all the middleware and finally is allowed call the controller action
 * @package Framework\Http\Routing
 */
final class RouteBuilder {
    /**
     * @var Router
     *
     * This is the router instance
     */
    private $router;

    /**
     * @var string
     *
     * Current URL
     */
    private $url;

    /**
     * @var array
     *
     * Contains URL data such as parameters and query string key-values
     */
    private $uri_data;

    /**
     * @var array
     *
     * List of middleware to apply to every route
     */
    private $global_middleware;

    /**
     * @var array
     *
     * Associate list of middleware which are applied to specific routes specified by the user
     */
    private $route_middleware;

    /**
     * @var BaseController
     *
     * View controller for the matched route
     */
    private $vc;

    /**
     * @var Request
     *
     * Current request
     */
    private $request;


    /**
     * @var Route
     *
     * Matched route instance
     */
    private $route;

    /**
     * RouteBuilder constructor.
     *
     * @param string $url - Current URL
     * @param array $uri_data - Contains URL data such as parameters and query string key-values
     * @param Route $route - Matched route instance
     * @param array $global_middleware - List of middleware to apply to every route
     * @param array $route_middleware - Associate list of middleware which are applied to specific routes specified by the user
     */
    public function __construct($url, $uri_data, $route, $global_middleware, $route_middleware) {
        $this->router = Router::instance();
        $this->url = $url;
        $this->uri_data = $uri_data;
        $this->route = $route;
        $this->global_middleware = $global_middleware;
        $this->route_middleware = $route_middleware;
        $this->request = new Request($this->route, $_SERVER["REQUEST_METHOD"], $this->uri_data["params"], $this->uri_data["query_strings"]);
    }

    /**
     * This builds the current route and call all the appropriate methods to prepare the route
     *
     * @return Response - Returns the response from either the middleware of the controller action
     * @throws KeyNotFoundException - Thrown is a route middleware name is not founds in the middleware list
     * @throws \Framework\Exceptions\UnexpectedStatusCode - Called is a response is created is an invalid status code
     */
    public function build() {
        // This checks to see whether the action is a function instead of a reference to a controller method
        if($this->route->isMethodCallable()) {
            $value = $this->route->getMethod()($this->request);
            $response = ($value instanceof Response) ? $value : new Response($value);
        } else {
            $this->checkRouteIsAuthenticated();
            $this->setUpViewController();

            $response = $this->preformAction();
        }

        return $response;
    }

    /**
     * This checks the requested route for -
     *                                      * If logging in is required,
     *                                      * If the user has the right permissions (if any)
     *                                      * If the user is in the right groups (if any)
     */
    private function checkRouteIsAuthenticated() {
        @session_start();
        /** @var AuthUser $auth_user */
        $auth_user = $_SESSION["auth_user"] ?? null;

        if ($this->route->isLoginRequired() and is_null($auth_user)) {
            Router::instance()->toURL($this->route->getRedirectRoute());
            return;
        }

        $permissions = $this->route->getPermissions();
        $groups = $this->route->getGroups();

        // If the route has no set permissions or groups then anyone can access it
        // Also if the user is a super user then he can access any route
        if((empty($permissions) and empty($groups)) or $auth_user->isSuperuser()) return;

        // TODO: check for permissions
        // TODO: check for groups

        // TODO: implement
    }

    /**
     * Sets the view controller according to which URL the user has navigated too
     */
    private function setUpViewController(){
        $cls = $this->route->getViewController();

        $this->vc = new $cls();

        if ($this->vc instanceof APIController) {
            $this->vc->init($this->url, $this->router, new APIView($this->vc, $this->router, $this->url));
        } elseif($this->vc instanceof AnnotationController) {
            $this->vc->init($this->url, $this->router, new ActionView($this->vc, $this->router, $this->url));
        } else {
            $this->vc->init($this->url, $this->router, new View($this->vc, $this->router, $this->url));
        }
    }

    /**
     * Retrieves the response from the controller method and returns it
     *
     * @return Response - Returns the response from either the middleware of the controller action
     * @throws KeyNotFoundException - Thrown is a route middleware name is not founds in the middleware list
     */
    private function preformAction() {
        $method = $this->route->getMethod();

        $middleware = $this->getMiddlewareList();

        if($middleware != []) {
            $middleware_queue = new MiddlewareQueue($middleware, function () use ($method) {
                return $this->vc->{($this->vc instanceof ClassController || $this->vc instanceof APIController) ? strtolower($_SERVER["REQUEST_METHOD"]) : $method}($this->request);
            });
            return $middleware_queue($this->request);
        } else {
            return  $this->vc->{($this->vc instanceof ClassController) ? strtolower($_SERVER["REQUEST_METHOD"]) : $method}($this->request);
        }
    }

    /**
     * This creates a single list composed of the global Middleware and all the Middleware assigned to the current route
     *
     * @return array - Returns the array of all the Middleware to be executed
     * @throws KeyNotFoundException - Thrown if a a middleware name was given but it doesn't exist
     */
    private function getMiddlewareList() {
        $middleware_execution_list = [];

        foreach ($this->global_middleware as $class) {
            array_push($middleware_execution_list, $class);
        }

        if($this->route->hasMiddleware()) {
            $middleware = $this->route->getMiddleware();

            foreach ($middleware as $name) {
                if(key_exists($name, $this->route_middleware)) {
                    array_push($middleware_execution_list, $this->route_middleware[$name]);
                } else {
                    throw new KeyNotFoundException("$name is not a registered middleware");
                }
            }
        }

        return $middleware_execution_list;
    }
}