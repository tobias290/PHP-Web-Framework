<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 16/02/2018
 * Time: 20:57
 */

namespace Framework\Http\Routing\Collections;

/**
 * Class RouteCollection
    * Stores a collection of routes
 * @package Framework\Http\Routing\Collections
 */
class RouteCollection implements \Countable, \Iterator {
    /**
     * @var array
     *
     * List of routes
     */
    protected $routes = [];

    /**
     * Adds a new route to the collection
     *
     * @param $route - Route to add
     */
    public function add($route) {
        $this->routes[] = $route;
    }

    /**
     * @return array - Returns all the routes
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * @return mixed - Returns the last route added
     */
    public function getLastRoute() {
        return $this->routes[count($this->routes) - 1];
    }

    /**
     * Returns a route with a specific name
     *
     * @param $name - Name of the route
     * @return mixed|null - Returns the route of null if none were found
     */
    public function getRouteFromName($name) {
        foreach ($this->routes as $route) {
            if($route->getName() == $name) return $route;
        }
        return null;
    }

    // ______________________________________________ Countable ________________________________________________________

    /**
     * @inheritdoc
     */
    public function count() {
        return count($this->routes);
    }

    // ___________________________________________ End  Countable ______________________________________________________

    // _____________________________________________ Iterator __________________________________________________________

    /**
     * @inheritdoc
     */
    public function current() {
        return current($this->routes);
    }

    /**
     * @inheritdoc
     */
    public function key() {
        return key($this->routes);
    }

    /**
     * @inheritdoc
     */
    public function next() {
        next($this->routes);
    }

    /**
     * @inheritdoc
     */
    public function rewind() {
        reset($this->routes);
    }

    /**
     * @inheritdoc
     */
    public function valid() {
        return key($this->routes) !== null;
    }

    // ____________________________________________ End Iterator _______________________________________________________
}