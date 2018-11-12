<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 14/05/2017
 * Time: 16:36
 */

namespace Framework\Controllers;

use Framework\Http\Requests\Request;
use Framework\Http\Responses\Response;
use Framework\Http\Routing\Router;
use Framework\Views\APIView;
use Framework\Views\View;

/**
 * Class BaseViewController
    * Base class for both API and normal view controller
 * @package Framework\Controllers
 */
abstract class BaseController {
    private $route;
    /** @var Router $router */
    protected $router;
    /** @var View | APIView $view */
    protected $view;

    /**
     * Initialises the controller
     * @param string $route - Route path
     * @param Router $router - Represents the router class
     * @param mixed $view - The view that loads the HTML
     */
    public function init($route, $router, $view){
        $this->route = $route;
        $this->router = $router;
        $this->view = $view;
    }

    /**
     * Returns the view instance associated with this controller
     * @return View
     */
    final public function getView() {
        return $this->view;
    }

    /** Gets the current page route as URI */
    final public function getCurrentRoute() {
        return $this->route;
    }
}