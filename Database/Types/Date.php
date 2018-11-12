<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:36
 */

namespace Framework\Database\Types;

use Framework\Forms\Types\Date as FormDate;

/**
 * Class Date
 * Represents the SQL type 'DATE'
 * @package Framework\Database
 */
final class Date extends AbstractType {
    protected $type_sql_value = "DATE";
    protected $form_class = FormDate::class;
}