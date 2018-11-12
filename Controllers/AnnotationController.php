<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 29/05/2017
 * Time: 19:32
 */

namespace Framework\Controllers;

use Framework\Http\Routing\Router;
use Framework\Views\View;

/**
 * Class ActionController
    * Action controller has a single 'GET' function but no 'POST' function
    * Instead it allows the user to have multiple GET, POST methods to a multifunction page
 * @package Framework\Controllers
 */
abstract class AnnotationController extends BaseController {
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

    /**
     * Returns the class method annotations which are needed to correctly redirect the user
     */
    public function getAnnotations() {
        $reflection_class = new \ReflectionClass(static::class);
        $class_annotations = [];

        foreach (get_class_methods(static::class) as $method) {
            $doc_comments = $reflection_class->getMethod($method)->getDocComment();
            // Allows the user to set annotations upper or lowercase
            // (Upper case is preferred)
            preg_match_all('#@(Path|Name|Methods|path|name|methods)(.*?)\n#s', $doc_comments, $annotations);

            if(@$annotations[1][0] !== null) {
                // Allows the user to set annotations (path, methods, name) in any order
                // Dynamically sets the key, value
                $class_annotations[$method] = [
                    strtolower($annotations[1][0]) => trim($annotations[2][0]),
                    strtolower($annotations[1][1]) => trim($annotations[2][1]),
                    strtolower($annotations[1][2]) => trim($annotations[2][2]),
                ];

                // Converts a comma separated string of methods into an array
                $class_annotations[$method]["methods"] = array_map(
                    function ($string) {
                        // Returns the string making sure its in upper case and trimmed of any whitespace
                        return trim(strtoupper($string));
                    },
                    explode(",", $class_annotations[$method]["methods"])
                );
            }
        }

        return $class_annotations;
    }
}