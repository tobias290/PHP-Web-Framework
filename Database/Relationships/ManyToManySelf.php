<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 27/06/2017
 * Time: 12:42
 */

namespace Framework\Database\Relationships;

use Framework\Exceptions\ClassNotImplemented;

/**
 * Class ManyToMany
 * This allows the user to make 'Many to Many' relationships with the same table
 * @package Framework\Database\Relationships
 */
//final class ManyToManySelf extends ManyToMany {
//    public function __construct($self, $joining_table_name) {
//        // NOTE: needs greater thought
//        throw new ClassNotImplemented("This has not yet been implemented");
//
//        parent::__construct($self, $self, $joining_table_name);
//    }
//}