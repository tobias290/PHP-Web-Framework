<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 16/02/2018
 * Time: 20:12
 */

namespace Framework\Http\Routing\Collections;

/**
 * Class PrefixRouteCollection
    * All routes in this collection must have the given prefix
 * @package Framework\Http\Routing\Collections
 */
final class PrefixRouteCollection extends RouteCollection { // TODO: implement countable, etc.
    /**
     * @var string
     *
     * Prefix that every route in this collection must have
     */
    private $prefix;


    /**
     * PrefixRouteCollection constructor.
     * @param string $prefix - Prefix that every route in this collection must have
     */
    public function __construct($prefix) {
        $this->prefix = $prefix;
    }

    /**
     * @return mixed - Returns the prefix associated with this route
     */
    public function getPrefix() {
        return $this->prefix;
    }
}