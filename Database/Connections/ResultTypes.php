<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 29/06/2017
 * Time: 13:23
 */

namespace Framework\Database\Connections;


final class ResultTypes {
    /** Constant for results as an associate array */
    const ASSOC = 1;

    /** Constant for results as a numbered array */
    const NUM = 2;

    /** Constant for results as an associate and numbered array */
    const BOTH = 3;
}