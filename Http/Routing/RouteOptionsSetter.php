<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 16/02/2018
 * Time: 21:09
 */

namespace Framework\Http\Routing;

/**
 * Class RouteOptionsSetter
    * This is returned when a route is created as this allowed specific settings to be applied to the newly created route
 * @package Framework\Http\Routing
 */
final class RouteOptionsSetter {
    /**
     * @var Route
     *
     * Route in which this class is for
     */
    private $route;

    /**
     * RouteOptionsSetter constructor.
     *
     * @param string $route - Route in which this class is for
     */
    public function __construct($route) {
        $this->route = $route;
    }

    /**
     * This gives the route a name so it can be accessed easily
     *
     * @param string $name - Name for the route
     * @return RouteOptionsSetter $this - Returns instance so multiple methods can be applied
     */
    public function name($name) {
        $this->route->setName($name);
        return $this;
    }

    /**
     * Sets middleware to this specific route to handle when this route is requested
     *
     * @param array ...$middleware - Middleware names relating to those set in route middleware
     * @return RouteOptionsSetter $this - Returns instance so multiple methods can be applied
     */
    public function middleware(...$middleware) {
        $this->route->setMiddleware($middleware);
        return $this;
    }

    /**
     * Requirements this route must match to be returned.
     *
     * @param array $requirements - List of requirements
     * @return RouteOptionsSetter $this - Returns instance so multiple methods can be applied
     */
    public function requirements($requirements) {
        $this->route->setRequirements($requirements);
        return $this;
    }

    /**
     * Set permissions so only users with certain permissions can access this route
     *
     * @param array ...$permissions - List of permissions names
     * @return RouteOptionsSetter $this - Returns instance so multiple methods can be applied
     */
    public function permissions(...$permissions) {
        $this->route->setPermissions($permissions);
        return $this;
    }

    /**
     * Sets groups so only user's in the specified groups can access this route
     *
     * @param array ...$groups - List of group names
     * @return RouteOptionsSetter $this - Returns instance so multiple methods can be applied
     */
    public function groups(...$groups) {
        $this->route->setGroups($groups);
        return $this;
    }

    /**
     * This sets login required to true for the specific route meaning only a logged in user can access it
     *
     * @param string|null $redirect_route - If the user is not logged in this is the route the user will be redirected to.
     *                                    - By default this is set to the 'login_url' set in the 'config.ini'
     *
     * @return $this - RouteOptionsSetter $this - Returns instance so multiple methods can be applied
     */
    public function loginRequired($redirect_route=null) {
        $this->route->setLoginRequired(true, $redirect_route);
        return $this;
    }
}