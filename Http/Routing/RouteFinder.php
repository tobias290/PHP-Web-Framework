<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 15/02/2018
 * Time: 20:14
 */

namespace Framework\Http\Routing;

use Framework\Exceptions\URLParameterDoesNotExist;

/**
 * Class RouteFinder
    * This takes the current URL and a given route URL and see's if they match
 * @package Framework\Http\Routing
 */
final class RouteFinder {
    /**
     * @var array
     *
     * List of data if the route was a match
     */
    private $route_data;

    /**
     * Attempts to match the given route to the current and if they are a match extract the needed data
     *
     * @param string $route_url - Route URL to match ageists current URL
     * @param string $current_url - Current URL
     * @param $requirements
     * @throws URLParameterDoesNotExist
     */
    public function find($route_url, $current_url, $requirements) {
        $this->matchURL($route_url, $current_url, $requirements);
    }

    /**
     * @return bool|mixed - Returns whether the given route matched the URL
     */
    public function isMatch() {
        return $this->route_data["is_match"] ?? false;
    }

    /**
     * @return array - Returns the route data
     */
    public function getRouteData() {
        return $this->route_data;
    }

    /**
     * Matches the path to the current URL to know which view controller to set up
     *
     * @param string $route_url - Represents the path set up by the user
     * @param string $current_url - Represents the URL currently at
     * @param array $requirements  Requirements (as regex) for the URL parameters
     * @throws URLParameterDoesNotExist - Thrown if a specified requirements is not in the $params list
     */
    private function matchURL($route_url, $current_url, $requirements){
        $regex_str = $this->createRegex($route_url);
        $current_url = parse_url($current_url);

        if(preg_match($regex_str, $current_url["path"], $matches)){
            $url_params = $this->extractURLParams($matches);

            if(!empty($requirements)) {
                $this->checkURLParamsRequirements($url_params, $requirements);
            }

            if(!empty($current_url["query"]))
                $url_query_strings = $this->extractURLQueryString($current_url["query"]);
            else
                $url_query_strings = null;

            $this->route_data = ["is_match" => true, "params" => $url_params, "query_strings" => $url_query_strings];
        } else {
            $this->route_data = ["is_match" => false, "params" => null, "query_strings" => null];
        }
    }

    /**
     * This function takes the URL format splits each part and turns it into a regex
     *
     * @param string $str - Represents the URL format
     * @return string $regex - a regex to match the URL to get get parameters
     */
    private function createRegex($str) {
        $regex = "";
        $split_str = explode("/", $str);
        $url_end_regex = "/?$#";

        foreach($split_str as $i => $part){
            if(preg_match_all("/{+(.*?)}/", $part, $matches)){
                $var = $matches[1][0];
                if($i == 0) $regex .= "#^"."/(?P<$var>[a-zA-Z0-9]+)";
                elseif($i == count($split_str) - 1) $regex .= "/(?P<$var>[a-zA-Z0-9]+)".$url_end_regex;
                else $regex .= "/(?P<$var>[a-zA-Z0-9]+)";
            } else {
                if($i == 0) $regex .= "#^".$part;
                elseif($i == count($split_str) - 1) $regex .= $part.$url_end_regex;
                else $regex .= "/".$part;
            }
        }
        return $regex;
    }

    /**
     * This extracts the paramters from the URL
     *
     * @param array $matches - Represents matches from the regex in 'matchURI'
     * @return array $url_params - Returns the URL parameters (specified by user in 'index_old.php') as a list
     */
    private function extractURLParams($matches){
        $url_params = array();

        foreach ($matches as $key => $value){
            if(is_string($key)){
                $url_params[$key] = $value;
            }
        }
        return $url_params;
    }

    /**
     * Compares the URL parameter value to a regex to make sure they match
     *
     * @param array $params - List of parameters and there value
     * @param array $requirements - List of parameter names and the regex they must conform to
     * @throws URLParameterDoesNotExist - Thrown if a specified requirements is not in the $params list
     */
    private function checkURLParamsRequirements($params, $requirements) {
        foreach ($requirements as $name => $regex) {
            if(key_exists($name, $params))
                $param = $params[$name];
            else
                throw new URLParameterDoesNotExist("'$name' is not a url parameter");

            if(!preg_match($regex, $param)) {
                // If any of them don't match we must raise a 404 error
                http_response_code(404);
                exit();
            }
        }
    }

    /**
     * Takes the URL parameters as a string and converts it into an associative array
     * E.g. $query_string = hello=world&foo=bar ... $query_string_array = ["hello" => "world, "foo" => "bar"]
     *
     * @param string $query_string - presents everything after the "?" in the URL
     * @return mixed $query_string_array - Returns the URL parameters as a associative array
     */
    private function extractURLQueryString($query_string) {
        parse_str($query_string, $query_string_array);
        return $query_string_array;
    }
}