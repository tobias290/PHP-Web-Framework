<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 31/12/2017
 * Time: 17:20
 */

namespace Framework\Security\Auth\Models;


use Framework\Database\DB;
use Framework\Database\Table;
use Framework\Database\Types\VarChar;

final class Permission extends Table {
    protected static $table_name = "auth_permission";

    /**
     * @var VarChar
     *
     * Required field up to 100 characters storing the name of the permission
     */
    public $name;

    /**
     * @var VarChar
     *
     * Required field up to 100 characters storing a code name for the permission to use to reference it
     */
    public $code_name;

    public function __construct() {
        parent::__construct();
        $this->name = new VarChar(["not_null" => true, "length" => 100]);
        $this->code_name = new VarChar(["not_null" => true, "length" => 100]);
    }
}