<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 14/05/2017
 * Time: 16:25
 */

namespace Framework\Controllers;

use Controllers\ClassViewController;
use Framework\Exceptions\MethodNotImplemented;
use Framework\Http\Requests\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Routing\Router;
use Framework\Views\APIView;

/**
 * Class APIController
    * Represents an API controller than either returns XML or JSON
 * @package Framework\Controllers
 */
abstract class APIController extends ClassController {
    private $route;
    /** @var APIView $view */
    protected $view;

    /**
     * Initialises the controller
     * @param string $route - Route path
     * @param Router $router - Represents the router class
     * @param APIView $view - The view that loads the JSON or XML
     */
    final public function init($route, $router, $view) {
        $this->route = $route;
        parent::init($route, $router, $view);
    }
}