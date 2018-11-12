<?php
/**
 * Created by PhpStorm.
 * User: toby
 * Date: 13/02/2018
 * Time: 12:36
 */

namespace Framework\Http\Middleware;

use Framework\Http\Requests\Request;


/**
 * Interface Middleware
     * Basic Middleware class that can be extended to custom user Middleware
 * @package Framework\Http\Middleware
 */
interface Middleware {

    /**
     * Method that must be implemented as it will be called when Middleware is instantiated
     * @param Request $request - Represents the HTTP request
     * @param \Closure $next - This is the Middleware queue instance to the user can call the next Middleware
     * @return mixed
     */
    public function handle($request, $next);
}