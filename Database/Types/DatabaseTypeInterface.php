<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 13:21
 */

namespace Framework\Database\Types;


/**
 * Interface DatabaseTypeInterface
    * Interface to method that must be created for a class that represents a SQL data type
 * @package Framework\Interfaces
 */
interface DatabaseTypeInterface {

    /**
     * Returns the type of the column
     * @return AbstractType | \Framework\Database\Integer
     */
    public function getType();

    /**
     * @return string
     */
    public function getFormClass();

    /**
     * Creates a column in SQL with all appropriate options
     * @param $name - Name of column
     * @param bool $is_next - Specifies whether there will be another column after current_columns
     * @return string - Returns the SQL for the column
     */
    public function getSQL($name, $is_next);
}