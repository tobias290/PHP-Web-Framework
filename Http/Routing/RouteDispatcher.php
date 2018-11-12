<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 16/02/2018
 * Time: 21:48
 */

namespace Framework\Http\Routing;

use Framework\Database\DB;
use Framework\Http\Routing\Collections\PrefixRouteCollection;
use Framework\Http\Routing\Collections\RouteCollection;
use Framework\Http\Routing\Collections\SubDomainRouteCollection;
use Framework\Http\Responses\ResponseNotFound;
use Framework\Security\Auth\Auth;

/**
 * Class RouteDispatcher
    * This class finds the route and sends the response
 * @package Framework\Http\Routing
 */
final class RouteDispatcher {
    /**
     * @var RouteCollection
     *
     * Collection of normal routes
     */
    private $normal_routes;

    /**
     * @var PrefixRouteCollection
     *
     * List of prefix route collection in which each route in a collection required a prefix
     */
    private $prefix_route_collections;

    /**
     * @var array
     *
     * List of sub domain route collection in which each route in a collection is part of a sub domain to the main HTTP host
     */
    private $sub_domain_route_collections;

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
     * RouteDispatcher constructor.
     *
     * @param RouteCollection $normal_routes - Collection of normal routes
     * @param array $prefix_route_collections - List of prefix route collection in which each route in a collection required a prefix
     * @param array $sub_domain_route_collections - List of sub domain route collection in which each route in a collection is part of a sub domain to the main HTTP host
     * @param array $global_middleware - List of middleware to apply to every route
     * @param array $route_middleware - Associate list of middleware which are applied to specific routes specified by the user
     */
    public function __construct($normal_routes, $prefix_route_collections, $sub_domain_route_collections, $global_middleware, $route_middleware) {
        $this->normal_routes = $normal_routes;
        $this->prefix_route_collections = $prefix_route_collections;
        $this->sub_domain_route_collections = $sub_domain_route_collections;
        $this->global_middleware = $global_middleware;
        $this->route_middleware = $route_middleware;

        // Give the Auth class the router instance
        Auth::setRouter(Router::instance());

        $this->setUpDatabase();
    }

    /**
     * Sets up the Database and adds all the tables to a list of instances
     *
     * @throws - Could throw error in the 'DB::connect()' method
     */
    private function setUpDatabase() {
        DB::connect();

        foreach (DB::tables() as $table) {
            DB::addTableInstance($table);
        }
    }

    /**
     * This gets the current URL and search every route to find the matching route
     * It will then send the response which by default if 404 not found, if a route is found the default response will be replaced
     */
    public function dispatch() {
        $url = $_SERVER["REQUEST_URI"];
        $http_method = $_SERVER["REQUEST_METHOD"];

        // Default response to HTTP 404 page not found
        // If any route is matched this will be changed to the correct response
        $response = new ResponseNotFound();

        /** @var Route $route */
        foreach ($this->normal_routes as $route) {
            if(!$route->hasHttpMethod($http_method)) continue;

            $finder = new RouteFinder();

            // This adds '/' to start and/or end if needed
            $path = "/{$route->getPath()}/";

            $finder->find($path, $url, $route->getRequirements());

            if($finder->isMatch()) {
                $url_data = $finder->getRouteData();

                $builder = new RouteBuilder($url, $url_data, $route, $this->global_middleware, $this->route_middleware);

                $response = $builder->build();
                goto send;
            }
        }

        /** @var PrefixRouteCollection $route_collection */
        foreach ($this->prefix_route_collections as $route_collection) {
            /** @var Route $route */
            foreach ($route_collection as $route) {
                if(!$route->hasHttpMethod($http_method)) continue;

                $finder = new RouteFinder();

                // This adds '/' to start and/or end if needed
                $path = "/{$route->getPath()}/";

                $finder->find($path, $url, $route->getRequirements());

                if ($finder->isMatch()) {
                    $url_data = $finder->getRouteData();

                    $builder = new RouteBuilder($url, $url_data, $route, $this->global_middleware, $this->route_middleware);

                    $response = $builder->build();
                    goto send;
                }
            }
        }

        // TODO: implement
//        /** @var SubDomainRouteCollection $route_collection */
//        foreach ($this->sub_domain_route_collections as $route_collection) {
//            /** @var Route $route */
//            foreach ($route_collection as $route) {
//                if(!$route->hasHttpMethod($http_method)) continue;
//
//                $finder = new RouteFinder();
//
//                $finder->find("/{$route_collection->get()}/{$route->getPath()}", $url, $route->getRequirements());
//
//                if ($finder->isMatch()) {
//                    $url_data = $finder->getRouteData();
//
//                    $builder = new RouteBuilder(Router::instance(), $url, $url_data, $route);
//
//                    $response = $builder->build();
//                    goto send;
//                }
//            }
//        }

        send:
            $response->send();
    }
}