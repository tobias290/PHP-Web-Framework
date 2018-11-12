<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 07/06/2017
 * Time: 12:10
 */

namespace Framework\Views;

use Framework\Controllers\AnnotationController;
use Framework\Exceptions\ActionDoesNotExist;
use Framework\Http\Routing\Router;

final class ActionView extends View {
    /**
     * View constructor.
     * @param AnnotationController $controller - Represents the view controller
     * @param Router $router - Represents the router class
     * @param string $route - The URL route of the current location
     */
    public function __construct($controller, $router, $route){
        parent::__construct($controller, $router, $route);
    }

    public function getRouteFromActionName($name) {
        foreach ($this->controller->getAnnotations() as $method) {
            if($method["name"] == $name)
                return $this->router->getURL(substr($method["path"], 1));
        }
        return null;
    }
}