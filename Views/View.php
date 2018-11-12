<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 10/01/2017
 * Time: 21:39
 */
namespace Framework\Views;

use Framework\Config;
use Framework\Controllers\{AnnotationController, BaseController};
use Framework\Exceptions\{BlockAlreadyExists, FileNotFound, NoBlockStarted, SectionNotDefined};
use Framework\Helpers\Attributes;
use Framework\Http\Responses\Response;
use Framework\Http\Responses\ResponseNotFound;
use Framework\Http\Routing\Router;
use Framework\TemplateEngine\TemplateEngine;

class View {
    use Attributes;

    private $is_extended = false;
    private $extended_file = null;

    private $context_processors = array();

    private $open_blocks = array();
    private $blocks = array();

    private $open_escapes = array();

    /** @var BaseController | AnnotationController */
    public $controller;
    public $router;
    public $route;

    /**
     * View constructor.
     * @param BaseController $controller - Represents the view controller
     * @param Router $router - Represents the router class
     * @param string $route - The URL route of the current location
     */
    public function __construct($controller, $router, $route){
        $this->controller = $controller;
        $this->router = $router;
        $this->route = $route;
    }

    /**
     * Returns true if the file is to be compiled with the template engine
     */
    private function isUsingFrameWorkTemplateLanguage() {
        if(Config::instance()->template == null)
            throw new SectionNotDefined("Section 'engine' must be defined in 'config.ini'");

        if(Config::instance()->template->engine == "framework_template_language") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Compiles the view with the template engine
     * @param string $file - The path of the file that is to be compiled
     * @return bool|string - Returns the new contents of the file
     */
    private function compileFileWithTemplateEngine($file) {
        $engine = new TemplateEngine("../" . $file);
        $engine->compile();

        return $engine->getFile();
    }

    /**
     * Registers a context processor with the view
     * @param $context_processor - Context processor to add
     */
    public function registerContextProcessor($context_processor) {
        array_push($this->context_processors, $context_processor);
    }

    /**
     * Loads the page as HTML and passes the variable to the file
     * @param string $file - File name
     * @param array $vars - Variables to pass to the HTML file
     * @return Response - Returns a a reponse with content and headers set to be sent by the router
     * @throws FileNotFound - TThrown if the requested file doesn't exist
     * @throws SectionNotDefined
     * @throws \Framework\Exceptions\UnexpectedStatusCode
     */
    public function load($file, $vars=[]) {
        $file = "views/$file";

        if(!file_exists($file))
            throw new FileNotFound("File not found: $file");

        // Gives the user another option to use the views builtin functions
        // $view->block() OR $this->block()
        $view = $this;

        foreach ($vars as $var => $value) {
            ${$var} = $value;
        }

        foreach ($this->context_processors as $context_processor) {
            foreach ($context_processor as $var => $value) {
                ${$var} = $value;
            }
        }

        // Start output buffering
        ob_start();

        // Either echo or require the html
        if ($this->isUsingFrameWorkTemplateLanguage()) {
            echo eval(" ?>" . $this->compileFileWithTemplateEngine($file) . "<?php ");
        } else {
            require_once($file);
        }

        if($this->is_extended) {
            $this->include($this->extended_file);
        }

        // Get the echoed or required file
        $content = ob_get_contents();

        // End output buffering
        ob_end_clean();

        // TODO: Create response, set headers, set content, return it

        return new Response($content);
    }

    /**
     * Lets the user return a selected HTTP response code
     * @param int $code - Code to be returned as reponse
     * @return int - Returns the code
     */
    public function http_response($code) {
        return http_response_code($code);
    }

    /**
     * Raises a HTTP 404 error
     *
     * @param string $message - Message to display with the response
     *
     * @return Response - Raised a 404 not found response
     */
    public function http_404_not_found($message = null) {
        return (new ResponseNotFound($message))->send();
    }

    /**
     * Returns - Cross-Site Request Forgery token
     */
    public function csrf_token() {
        @session_start();
        if(empty($_SESSION["token"])) {
            $_SESSION["token"] = bin2hex(random_bytes(32));
        }
        $token = $_SESSION["token"];

        return "<input type='hidden' name='token' value='$token'>";
    }

    /**
     * Returns the current route
     */
    public function getCurrentRoute(){
        return $this->route;
    }

    /**
     * Returns an anchor tag
     * @param string $anchor_name - Name to give the route (What to put between the '<a><\a>' tags)
     * @param $route_name - Name of the route
     * @param array $params - Any URL parameters needed
     * @param array $attributes - Any other attributes to add to the tag
     * @return string - Returns the anchor tag
     */
    public function anchor($anchor_name, $route_name, $params=[], $attributes=[]) {
        $route = $this->router->getRouteFromName($route_name, $params);

        return "<a href='$route' " . $this->insertAttributes($attributes) . ">$anchor_name</a>";
    }

    /**
     * Used in the HTML to include another HTML file
     * @param $file - Name of file to include
     * @param array $with - Variables to pass to HTML file
     * @return mixed|null - Returns the included file
     * @throws FileNotFound - Thrown if the requested file doesn't exist
     */
    public function include($file, $with=[]) {
        $file = "$file";

        if(file_exists($file)) {
            if(!empty($with)) {
                foreach ($with as $var => $value){
                    ${$var} = $value;
                }
            }
            return require_once($file);
        } else{
            throw new FileNotFound("File not found: $file");
        }
    }

    /**
     * Takes a file path and returns a new path with it static destination
     * @param string $file - File to include
     * @return string - Returns new file path
     * @throws FileNotFound - Thrown if static file cannot be found
     */
    public function static($file) {
        if(!file_exists("$file")) {
            throw new FileNotFound("File not found: $file");
        }

        return $_SERVER["HTTP_HOST"] . "/public/$file";
    }

    /**
     * Alerts the view that is being extended
     * @param string $file - File to extend
     */
    public function extend($file){
        $this->is_extended = true;
        $this->extended_file = $file;
    }

    /**
     * Method to be used in template to define a block
     * @param string $name - Name of the block
     */
    public function define($name) {
        if(array_key_exists($name, $this->blocks)) {
            echo $this->blocks[$name];
        }
    }

    /**
     * Starts a block which is piece of code to be inserted into a template
     * @param string $block_name - Name of the block
     * @throws BlockAlreadyExists - Thrown if that block already exists
     */
    public function block($block_name) {
        // NOTE: may cause a problem with overlapping blocks
        if(in_array($block_name, $this->open_blocks)) {
            throw new BlockAlreadyExists("$block_name already exists, that name cannot be used");
        }

        $this->open_blocks[] = $block_name;

        ob_start();
        ob_implicit_flush(0);
    }

    /**
     * Ends a block and saves it
     * @throws NoBlockStarted - Throws if a block was not started
     */
    public function endblock() {
        // Checks to see if the user is trying to close a block when one was never opened
        if(empty($this->open_blocks)) {
            throw new NoBlockStarted("A block hasn't been started");
        }

        $block = ob_get_clean();
        $this->blocks[array_pop($this->open_blocks)] = $block;
    }

    /**
     * Sets a block which has short text to save space (e.g. a title)
     * @param string $block_name - Name of the block
     * @param string $content - Content of the block
     * @throws BlockAlreadyExists - Thrown if the block already exists
     */
    public function set($block_name, $content) {
        // NOTE: may cause a problem with overlapping blocks
        if(in_array($block_name, $this->open_blocks)) {
            throw new BlockAlreadyExists("$block_name already exists, that name cannot be used");
        }

        $this->blocks[$block_name] = $content;
    }

    /**
     * Starts a block to not escape code
     * @param $escape - Defines whether to escape the block
     */
    public function autoescape($escape="on") {
        if($escape == "off") {
            $this->open_escapes[] = $escape;
            ob_start();
            ob_implicit_flush(0);
        }
    }

    /**
     * Ends the escape block and echos the not escaped code
     */
    public function endautoescape() {
        if(array_pop($this->open_escapes) == "off")
            echo htmlspecialchars(ob_get_clean());
        else
            echo ob_get_clean();
    }

    /**
     * Starts a block to remove all white space
     */
    public function spaceless() {
        ob_start();
        ob_implicit_flush(0);
    }


    /**
     * Returns the block without white space
     */
    public function endspaceless() {
        $block = preg_replace('/\s+/', '', ob_get_clean());

        // New line added at the end to better looking code (in HTML)
        echo $block."\n";
    }
}