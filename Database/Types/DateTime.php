<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:38
 */

namespace Framework\Database\Types;

use Framework\Forms\Types\DateTimeLocal;


/**
 * Class DateTime
 * Represents the SQL type 'DATETIME'
 * @package Framework\Database
 */
final class DateTime extends AbstractType {
    protected $type_sql_value = "DATETIME";
    protected $form_class = DateTimeLocal::class;
}