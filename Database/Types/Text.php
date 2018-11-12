<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:38
 */

namespace Framework\Database\Types;

use Framework\Forms\Types\TextArea;

final class Text extends AbstractType {
    protected $type_sql_value = "TEXT";
    protected $form_class = TextArea::class;
}