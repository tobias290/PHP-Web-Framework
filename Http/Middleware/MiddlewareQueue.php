<?php
/**
 * Created by PhpStorm.
 * User: toby
 * Date: 13/02/2018
 * Time: 12:36
 */

namespace Framework\Http\Middleware;

use Framework\Http\Requests\Request;
use Framework\Http\Responses\Response;

/**
 * Class MiddlewareQueue
    * This takes a list of middleware and calls each one
 * @package Framework\Http\Middleware
 */
final class MiddlewareQueue {
    /**
     * @var array
     *
     * List of middleware to call
     */
    private $middleware;

    /**
     * @var \Closure
     *
     * Once the last middleware is called this closure will call the correct controller action
     */
    private $view_closure;

    /**
     * MiddlewareQueue constructor.
     *
     * @param $middleware - List of middleware to call
     * @param \Closure $view_closure - Closure that calls the correct controller action
     */
    public function __construct($middleware, \Closure $view_closure) {
        $this->middleware = $middleware;
        $this->view_closure = $view_closure;
    }

    /**
     * This pops of the current middleware in the queue calls it, then delegates control to that middleware
     * Each middleware will call this method therefore nothing will be returned till either a middleware exists early or the $view_closure is called
     *
     * @param Request $request - Current request
     * @return Response - Returns a response to call sent
     */
    public function __invoke(Request $request) {
        $cls = array_shift($this->middleware);
        $is_last = count($this->middleware) < 1;

        /** @var Middleware $middleware */
        $middleware = new $cls();

        $response = $middleware->handle($request, function () use ($is_last, $cls, $request) {
            // If the current middleware is the last in the list returns the view otherwise return the next middleware
            return $is_last ? ($this->view_closure)($request) : $this($request);
        });

        return $response;
    }
}