<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 16/02/2018
 * Time: 20:13
 */

namespace Framework\Http\Routing\Collections;

/**
 * Class SubDomainRouteCollection
    * All routes in this collection must be under the sub domain
 * @package Framework\Http\Routing\Collections
 */
class SubDomainRouteCollection extends RouteCollection {
    /**
     * @var string
     *
     * Sub domain every route in this collection must be apart of
     */
    private $sub_domain;

    /**
     * SubDomainRouteCollection constructor.
     * @param string $sub_domain - Sub domain every route in this collection must be apart of
     */
    public function __construct($sub_domain) {
        $this->sub_domain = $sub_domain;
    }
}