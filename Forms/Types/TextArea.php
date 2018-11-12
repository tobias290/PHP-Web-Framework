<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 25/06/2017
 * Time: 17:43
 */

namespace Framework\Forms\Types;


final class TextArea extends AbstractElement {
    private $rows, $columns, $default;

    /**
     * TextArea constructor.
     * @param string | int $rows - Number of rows
     * @param string | int $columns - Number of columns
     * @param array $attributes - Other attributes to add
     * @param string | null $default - Default text
     */
    public function __construct($rows, $columns, $attributes=[], $default=null) {
        $this->rows = $rows;
        $this->columns = $columns;
        $this->attributes = $attributes;
        $this->default = $default;
    }

    public function __toString() {
        if(key_exists("label", $this->attributes))
            return "<label>{$this->attributes['label']}</label></label><textarea name='$this->name' rows='$this->rows' cols='$this->columns' " . $this->insertAttributes() . ">$this->default</textarea>";
        else
            return "<textarea name='$this->name' rows='$this->rows' cols='$this->columns' " . $this->insertAttributes() . ">$this->default</textarea>";
    }
}