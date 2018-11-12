<?php
/**
 * Created by PhpStorm.
 * User: toby
 * Date: 13/02/2018
 * Time: 12:35
 */

namespace Framework\Http\Routing;


use Framework\Config;
use Framework\Controllers\BaseController;

/**
 * Class Route
    * This represents a single route (moved from being a associative array)
 * @package Framework\Routing
 */
final class Route {
    /**
     * @var string
     *
     * Path of this route (e.g. /path/)
     */
    private $path;

    /**
     * @var BaseController
     *
     * View controller for this route
     */
    private $view_controller;

    /**
     * @var string
     *
     * Method for this route
     */
    private $method;

    /**
     * @var array
     *
     * List of HTTP methods that this route allows
     */
    private $http_methods;

    /**
     * @var string
     *
     * Name of the route
     * Names it easier to get and access the route
     */
    private $name = null;

    /**
     * @var array | null
     *
     * Only used if view controller is an action view controller
     * This will hold all the annotations for the different actions of the view controller
     */
    private $annotations;

    /**
     * @var array
     *
     * List of requirements the route needs to meet in order to be valid
     */
    private $requirements = [];

    /**
     * @var array
     *
     * List holding names of Middleware to execute on this route
     */
    private $middleware = [];

    /**
     * @var array
     *
     * List of all permissions the user needs to access this route
     */
    private $permissions = [];

    /**
     * @var array
     *
     * List of all groups the user needs to access this route
     */
    private $groups = [];

    /**
     * @var bool
     *
     * Tells the router whether the user needs to be logged in to access this route
     */
    private $login_required = false;

    /**
     * @var string | null
     *
     * If login is required then this is route to redirect too if they aren't logged in
     */
    private $redirect_route = null;

    /**
     * Route constructor.
     * @param string $path - Physical URL path for this route
     * @param string $view_controller - View controller to associate with this route

     * @param array | null $annotations - Annotation of the view controller (only used if view controller is an action view controller)
     */

    /**
     * Route constructor.
     * @param $path - Physical URL path for this route
     * @param $view_controller - View controller to associate with this route
     * @param $method - The method to call when this route is current route
     * @param $http_methods - List of HTTP methods that this route can be called on
     */
    public function __construct($path, $view_controller, $method, $http_methods) {
        $this->path = $path;
        $this->view_controller = $view_controller;
        $this->method = $method;
        $this->http_methods = $http_methods;
    }

    /**
     * @return mixed
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getViewController() {
        return $this->view_controller;
    }

    /**
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getHttpMethods(): array {
        return $this->http_methods;
    }

    /**
     * Returns whether this route is permitted for a certain HTTP method
     *
     * @param string $method - The HTTP method to check for in this route
     * @return boolean - Returns whether this route has the given HTTP method
     */
    public function hasHttpMethod($method) {
        return in_array($method, $this->http_methods);
    }

    /**
     * @return bool
     */
    public function isMethodCallable() {
        return is_callable($this->method);
    }

    /**
     * @return mixed
     */
    public function getAnnotations() {
        return $this->annotations;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name) {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function getRequirements(): array {
        return $this->requirements;
    }

    /**
     * Add requirements for the URL parameters as regular expressions
     * @param array $requirements - Array of url parameters and a regex to match them to
     */
    public function setRequirements($requirements) {
        $this->requirements = array_merge($this->requirements, $requirements);
    }

    /**
     * @return array
     */
    public function getMiddleware(): array {
        return $this->middleware;
    }

    /**
     * @param array $middleware
     */
    public function setMiddleware($middleware) {
        $this->middleware = array_merge($this->middleware, $middleware);
    }

    /**
     * Returns whether there is any Middleware set
     * @return bool
     */
    public function hasMiddleware() {
        return !empty($this->middleware);
    }

    /**
     * @return array - Returns a the current groups assigned permissions
     */
    public function getPermissions() {
        return $this->permissions;
    }

    /**
     * Sets the current route's permissions
     * @param array $permissions - List of permissions to set
     */
    public function setPermissions($permissions) {
        $this->permissions = array_merge($this->permissions, $permissions);
    }

    /**
     * @return array - Returns a the current groups assigned groups
     */
    public function getGroups() {
        return $this->groups;
    }

    /**
     * Sets the current route's groups
     * @param array $groups - List of groups to set
     */
    public function setGroups($groups) {
        $this->groups = array_merge($this->groups, $groups);
    }

    /**
     * @return bool - Returns whether the user needs to be logged it to access this route
     */
    public function isLoginRequired() {
        return $this->login_required;
    }

    /**
     * @param boolean $login_required - Sets the login required
     * @param string $redirect_route - The route to redirect the user to if the user is not logged in
     */
    public function setLoginRequired($login_required, $redirect_route) {
        $this->login_required = $login_required;

        if(is_null($redirect_route)) {
            $this->redirect_route = Config::instance()->app->login_url;
        } else {
            $this->redirect_route = $redirect_route;
        }
    }

    /**
     * @return null|string - This is route to redirect too if they aren't logged in
     */
    public function getRedirectRoute() {
        return $this->redirect_route;
    }
}