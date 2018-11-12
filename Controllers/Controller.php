<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 10/01/2017
 * Time: 19:30
 */

namespace Framework\Controllers;

use Framework\Exceptions\MethodNotImplemented;
use Framework\Http\Requests\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Routing\Router;
use Framework\Views\View;

/**
 * Class ViewController
    * Class for a normal view controller that has 'GET' and 'POST' Requests
 * @package Framework\Controllers
 */
abstract class Controller extends BaseController {
    private $route;

    /**
     * Initialises the controller
     * @param string $route - Route path
     * @param Router $router - Represents the router class
     * @param View $view - The view that loads the HTML
     */
    final public function init($route, $router, $view){
        parent::init($route, $router, $view);
        $this->route = $route;
    }
}