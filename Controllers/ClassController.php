<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 17/02/2018
 * Time: 17:19
 */

namespace Framework\Controllers;


use Framework\Exceptions\MethodNotImplemented;
use Framework\Http\Requests\Request;
use Framework\Http\Responses\Response;

/**
 * Class ClassViewController
    * This class is a way of implement a controller where a single class will handle all the different HTTP method but with a separate function for each
 * @package Framework\Controllers
 */
abstract class ClassController extends BaseController {
    /**
     * Function that must be used the user which represents a 'GET' request
     * @param Request $request - Represents a request and has all main important function
     * @return Response
     */
    abstract public function get(Request $request);

    /**
     * Function that can optionally be used the a 'POST' request is received
     *
     * @param Request $request - Represents a request and has all main important function
     * @throws MethodNotImplemented
     * @throws \ReflectionException
     */
    public function post(Request $request){
        $this->implementable(__FUNCTION__);
    }

    /**
     * Function that can optionally be used the a 'PUT' request is received
     *
     * @param Request $request - Represents a request and has all main important function
     * @throws MethodNotImplemented
     * @throws \ReflectionException
     */
    public function put(Request $request) {
        $this->implementable(strtoupper(__FUNCTION__));
    }

    /**
     * Function that can optionally be used the a 'PATCH' request is received
     *
     * @param Request $request - Represents a request and has all main important function
     * @throws MethodNotImplemented
     * @throws \ReflectionException
     */
    public function patch(Request $request) {
        $this->implementable(strtoupper(__FUNCTION__));
    }

    /**
     * Function that can optionally be used the a 'DELETE' request is received
     *
     * @param Request $request - Represents a request and has all main important function
     * @throws MethodNotImplemented
     * @throws \ReflectionException
     */
    public function delete(Request $request) {
        $this->implementable(strtoupper(__FUNCTION__));
    }

    /**
     * Function that can optionally be used the a 'OPTIONS' request is received
     *
     * @param Request $request - Represents a request and has all main important function
     * @throws MethodNotImplemented
     * @throws \ReflectionException
     */
    public function options(Request $request) {
        $this->implementable(strtoupper(__FUNCTION__));
    }

    /**
     * This method is used in classes that are optional to implement but will throw an error if it called and not overridden
     *
     * @param $method - Method name
     * @throws MethodNotImplemented - Thrown if the user sends a post request to a view
     *                                    controller that has not implemented the post method
     * @throws \ReflectionException
     */
    private function implementable($method) {
        $reflection_method = new \ReflectionMethod(get_called_class(), $method);

        try {
            // If this is successful it means this method as been overridden
            $reflection_method->getPrototype();
            return;
        } catch (\Exception $e) {
            // If an error occurs it means the method was not defines in the subclass therefore PostMethodNotImplemented should be raised
            throw new MethodNotImplemented("$method method not implemented for " . get_called_class());
        }
    }
}