<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 01/01/2018
 * Time: 00:28
 */

namespace Framework\Security\Auth\Models;


use Framework\Database\Relationships\ManyToMany;
use Framework\Database\Table;
use Framework\Database\Types\VarChar;

/**
 * Class Group
    * Represents an authentication group
 * @package Framework\Security\Auth\Models
 */
final class Group extends Table {
    protected static $table_name = "auth_group";

    /**
     * @var VarChar
     *
     * Required field up to 100 characters storing a name for this group
     */
    public $name;

    /**
     * @var ManyToMany
     *
     * Many to many relationship to 'Permission' storing all permissions for this group
     */
    public $permissions;

    public function __construct() {
        parent::__construct();
        $this->name = new VarChar(["not_null" => true, "length" => 100]);
        $this->permissions = new ManyToMany(self::class, Permission::class, "auth_group_permissions");

        // TODO: create many to many relationship to permission model
    }
}