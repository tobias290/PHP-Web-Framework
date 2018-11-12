<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 15/02/2018
 * Time: 21:27
 */

namespace Framework\Http\Routing;


use Framework\Controllers\APIController;
use Framework\Controllers\ClassController;
use Framework\Exceptions\InvalidHttpMethodException;
use Framework\Http\Responses\{ResponsePermanentRedirect, ResponseRedirect};
use Framework\Http\Routing\Collections\{PrefixRouteCollection, RouteCollection, SubDomainRouteCollection};

/**
 * Class Router
    * This class is where routes are defined along with any middleware that will be used
 * @package Framework\Http\Routing
 */
final class Router {
    /**
     * @var Router
     *
     * Class instance so it can be accessed in static methods
     */
    private static $instance;

    /**
     * @var array
     *
     * List of middleware to be executed on every route
     */
    private $global_middleware = [];

    /**
     * @var array
     *
     * An associative list of middleware which can be referenced on specific routes
     */
    private $route_middleware = [];

    /**
     * @var array
     *
     * List of all the available HTTP methods (verbs)
     */
    private $http_methods = [
        "GET",
        "POST",
        "PUT",
        "PATCH",
        "DELETE",
        "OPTIONS"
    ];

    /**
     * @var RouteCollection
     *
     * Collection on any route that does not require a prefix or is not part of a sub domain
     */
    private $normal_route_collection;

    /**
     * @var array
     *
     * Collection of sub domain collections
     */
    private $sub_domain_collections = [];

    /**
     * @var array
     *
     * Collection of prefixn collections
     */
    private $prefix_collections = [];

    /**
     * @var bool
     *
     * While true it will append and new route to a sub domain collection
     */
    private $is_sub_domain_collection = false;

    /**
     * @var bool
     *
     * While true it will append and new route to a prefix collection
     */
    private $is_prefix_collection = false;

    private $middleware;

    private $is_middleware_group;

    /**
     * Router constructor.
     */
    public function __construct() {
        $this->normal_route_collection = new RouteCollection();

        self::$instance = $this;
    }

    /**
     * @return Router - Returns instance
     */
    public static function instance() {
        if(self::$instance === null)
            self::$instance = new Router();
        return self::$instance;
    }

    /**
     * Assigns the global middleware
     *
     * @param $middleware - List of middleware to apply to every route
     */
    public static function globalMiddleware($middleware) {
        self::instance()->global_middleware = $middleware;
    }

    /**
     * Assigns route middleware
     *
     * @param $middleware - Associate list of middleware can can be assigned to specific routes
     */
    public static function routeMiddleware($middleware) {
        self::instance()->route_middleware = $middleware;
    }

    /**
     * Any defined routes in the callback will have the given middleware applied to them
     *
     * @param $middleware - List of middleware to apply to every route in the callback
     * @param $callback - Function with route definitions in which the middleware will be applied to
     */
    public static function group($middleware, $callback) {
        self::instance()->middleware = $middleware;

        self::instance()->is_middleware_group = true;

        $callback();

        self::instance()->is_middleware_group = false;
        self::instance()->middleware = null;
    }

    /**
     * Creates a sub domain to the current HTTP_HOST
     *
     * @param $name - Name of the sub domain
     * @param $callback - Function with routes to only work with the sub domain
     */
    public static function sub($name, $callback) {
        self::instance()->sub_domain_collections[] = new SubDomainRouteCollection($name);

        self::instance()->is_sub_domain_collection = true;

        $callback();

        self::instance()->is_sub_domain_collection = false;
    }

    /**
     * Creates a prefix
     *
     * @param $prefix - The prefix
     * @param $callback - Function with routes to append the prefix to
     */
    public static function prefix($prefix, $callback) {
        self::instance()->prefix_collections[] = new PrefixRouteCollection(trim($prefix, "/"));

        self::instance()->is_prefix_collection = true;

        $callback();

        self::instance()->is_prefix_collection = false;

    }

    /**
     * This adds a route that can be called to any HTTP method
     *
     * @param $path - URL path of this route
     * @param $action - Action to apply then this route is given (either function of 'controller#method')
     * @return RouteOptionsSetter - Returns an RouteOptionsSetter instance so various requirements/ options can be added
     * @throws InvalidHttpMethodException - Thrown if a invalid HTTP method was passed
     */
    public static function any($path, $action) {
        return self::instance()->add(self::instance()->http_methods, $path, $action);
    }

    /**
     * This adds a route that can be called to any HTTP method
     *
     * @param $methods - List of methods this route can be called on
     * @param $path - URL path of this route
     * @param $action - Action to apply then this route is given (either function of 'controller#method')
     * @return RouteOptionsSetter - Returns an RouteOptionsSetter instance so various requirements/ options can be added
     * @throws InvalidHttpMethodException - Thrown if a invalid HTTP method was passed
     */
    public static function match($methods, $path, $action) {
        return self::instance()->add($methods, $path, $action);
    }

    /**
     * This adds a route that can be called on the GET HTTP method
     *
     * @param $path - URL path of this route
     * @param $action - Action to apply then this route is given (either function of 'controller#method')
     * @return RouteOptionsSetter - Returns an RouteOptionsSetter instance so various requirements/ options can be added
     * @throws InvalidHttpMethodException - Thrown if a invalid HTTP method was passed
     */
    public static function get($path, $action) {
        return self::instance()->add(["GET"], $path, $action);
    }

    /**
     * This adds a route that can be called on the POST HTTP method
     *
     * @param $path - URL path of this route
     * @param $action - Action to apply then this route is given (either function of 'controller#method')
     * @return RouteOptionsSetter - Returns an RouteOptionsSetter instance so various requirements/ options can be added
     * @throws InvalidHttpMethodException - Thrown if a invalid HTTP method was passed
     */
    public static function post($path, $action) {
        return self::instance()->add(["POST"], $path, $action);
    }

    /**
     * This adds a route that can be called on the POST HTTP method
     *
     * @param $path - URL path of this route
     * @param $action - Action to apply then this route is given (either function of 'controller#method')
     * @return RouteOptionsSetter - Returns an RouteOptionsSetter instance so various requirements/ options can be added
     * @throws InvalidHttpMethodException - Thrown if a invalid HTTP method was passed
     */
    public static function put($path, $action) {
        return self::instance()->add(["PUT"], $path, $action);
    }

    /**
     * This adds a route that can be called on the PATCH HTTP method
     *
     * @param $path - URL path of this route
     * @param $action - Action to apply then this route is given (either function of 'controller#method')
     * @return RouteOptionsSetter - Returns an RouteOptionsSetter instance so various requirements/ options can be added
     * @throws InvalidHttpMethodException - Thrown if a invalid HTTP method was passed
     */
    public static function patch($path, $action) {
        return self::instance()->add(["PATCH"], $path, $action);
    }

    /**
     * This adds a route that can be called on the DELETE HTTP method
     *
     * @param $path - URL path of this route
     * @param $action - Action to apply then this route is given (either function of 'controller#method')
     * @return RouteOptionsSetter - Returns an RouteOptionsSetter instance so various requirements/ options can be added
     * @throws InvalidHttpMethodException - Thrown if a invalid HTTP method was passed
     */
    public static function delete($path, $action) {
        return self::instance()->add(["DELETE"], $path, $action);
    }

    /**
     * This adds a route that can be called on OPTIONS HTTP method
     *
     * @param $path - URL path of this route
     * @param $action - Action to apply then this route is given (either function of 'controller#method')
     * @return RouteOptionsSetter - Returns an RouteOptionsSetter instance so various requirements/ options can be added
     * @throws InvalidHttpMethodException - Thrown if a invalid HTTP method was passed
     */
    public static function options($path, $action) {
        return self::instance()->add(["OPTIONS"], $path, $action);
    }

    /**
     * This is the entry point once this is called the application will find the correct route and call the appropriate action
     */
    public static function request() {
        $dispatcher = new RouteDispatcher(
            self::instance()->normal_route_collection,
            self::instance()->prefix_collections,
            self::instance()->sub_domain_collections,
            self::instance()->global_middleware,
            self::instance()->route_middleware
        );

        $dispatcher->dispatch();
    }

    /**
     * This adds the route to the appropriate list
     *
     * @param $http_methods - List of HTTP method this route can be found
     * @param $path - URL path of the route
     * @param $callback - Either a function or a string with the vc#method
     * @return RouteOptionsSetter - Returns an RouteOptionsSetter instance so various requirements/ options can be added
     * @throws InvalidHttpMethodException - Thrown if a invalid HTTP method was passed
     */
    private function add($http_methods, $path, $callback) {
        // Convert the methods to uppercase
        array_walk($http_methods, function (&$e) {$e = strtoupper($e);});

        // This check to make sure the HTTP method is valid
        foreach ($http_methods as $method) {
            if(!in_array($method, $this->http_methods)) {
                throw new InvalidHttpMethodException("'$method' is an invalid HTTP method");
            }
        }

        // Removes '/' from either side of the path
        $path = trim($path, "/");

        $vc = null;
        $method = null;

        // If the callback isn't callable then a string was passed referring to an action in a controller
        if(!is_callable($callback)) {
            $split_action = explode("#", $callback);

            // Converts to fully qualified namespace
            $vc = "App\Controllers\\" . $split_action[0];

            $method = get_parent_class($vc) == ClassController::class || get_parent_class($vc) == APIController::class ? "" : $split_action[1];
        }

        $route = new Route(self::instance()->is_prefix_collection ? $this->getPathWithPrefix($path) : $path, $vc ?? null, $method ?? $callback, $http_methods);

        if(self::instance()->is_prefix_collection) {
            self::instance()->prefix_collections[count(self::instance()->prefix_collections) - 1]->add($route);
        } elseif (self::instance()->is_sub_domain_collection) {
            self::instance()->sub_domain_collections[count(self::instance()->sub_domain_collections) - 1]->add($route);
        } else {
            self::instance()->normal_route_collection->add($route);
        }

        return (
            self::instance()->is_middleware_group ?
            (new RouteOptionsSetter($route))->middleware(self::instance()->middleware) :
            new RouteOptionsSetter($route)
        );
    }

    /**
     * This gets the path with the prefix form the last created sub domain collection
     *
     * @param $path - Path to append prefix to
     * @return string - Returns the new path with the appended prefix
     */
    private function getPathWithPrefix($path) {
        return self::instance()->prefix_collections[count(self::instance()->prefix_collections) - 1]->getPrefix() . "/" . $path;
    }

    // _______________________________________________ HELPERS _________________________________________________________

    /**
     * Returns the route according to it name set up by the user
     *
     * @param string $name - Name of route the user wants to navigate too
     * @return Route | null - Either returns the route array or null
     */
    private function getRoute($name){
        /** @var Route $route */
        foreach ($this->normal_route_collection as $route) {
            if($route->getName() == $name) return $route;
        }

        /** @var PrefixRouteCollection $route_collection */
        foreach ($this->prefix_collections as $route_collection) {
            /** @var Route $route */
            foreach ($route_collection as $route) {
                if($route->getName() == $name) return $route;
            }
        }

        // TODO: implement
        /** @var SubDomainRouteCollection $route_collection */
        foreach ($this->sub_domain_collections as $route_collection) {
            /** @var Route $route */
            foreach ($route_collection as $route) {
                if($route->getName() == $name) return $route;
            }
        }

        return null;
    }

    /**
     * Gets the route from a name and any parameters needed and returns the URL as a string (for use in HTML or view controller)
     *
     * @param string $name - Name of path and view controller to search for
     * @param array $params - Any URL parameters needed
     * @return string - Returns the URL as a string after adding the parameters to the URL
     */
    public function getRouteFromName($name, $params=[]) {
        $route = $this->getRoute($name);
        $new_route = $route->getPath();

        foreach ($params as $key => $value){
            $new_route = str_replace("{".$key."}", $value, $route->getPath());
        }
        return "http://{$_SERVER["HTTP_HOST"]}/$new_route";
    }

    /**
     * Redirects to the page specified by $name and $params
     * Similar to 'getRouteFromName()' but redirects to the page instead of returning the URL
     *
     * @param string $name - Name of path and view controller to search for
     * @param array $params - Any URL parameters needed
     * @return ResponseRedirect - Returns a redirect response
     * @throws \Framework\Exceptions\UnexpectedStatusCode - Thrown if a status code given is unexpected or incorrect
     */
    public function redirect($name, $params=[]) {
        $route = $this->getRoute($name);
        $new_route = $route->getPath();

        foreach ($params as $key => $value){
            $new_route = str_replace("{".$key."}", $value, $route->getPath());
        }

        $response = new ResponseRedirect();
        $response->setHeader("Location","http://{$_SERVER["HTTP_HOST"]}/$new_route");

        return $response;
    }

    /**
     * Redirects to the page specified by $name and $params but is a permanent redirect therefore the status code is 301
     * Similar to 'getRouteFromName()' but redirects to the page instead of returning the URL
     *
     * @param string $name - Name of path and view controller to search for
     * @param array $params - Any URL parameters needed
     * @return ResponsePermanentRedirect - Returns a permanent redirect response
     * @throws \Framework\Exceptions\UnexpectedStatusCode - Thrown if a status code given is unexpected or incorrect
     */
    public function permanentRedirect($name, $params=[]) {
        $route = $this->getRoute($name);
        $new_route = $route->getPath();

        foreach ($params as $key => $value){
            $new_route = str_replace("{".$key."}", $value, $route->getPath());
        }

        $response = new ResponsePermanentRedirect();
        $response->setHeader("Location","http://{$_SERVER["HTTP_HOST"]}/$new_route");

        return $response;
    }

    /**
     * Function to redirect to a URL
     *
     * @param $url - URL to redirect to
     */
    public function toURL($url) {
        header("Location: $url");
    }
}