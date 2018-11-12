<?php
/**
 * Created by PhpStorm.
 * User: toby
 * Date: 12/12/2017
 * Time: 19:11
 */

namespace Framework\Security\Auth\Models;


use Framework\Database\DB;
use Framework\Database\Relationships\ManyToMany;
use Framework\Database\Table;
use Framework\Database\Types\{
    Boolean, DateTime, VarChar
};

/**
 * Class User
    * Class to represent users
 * @package Framework\Security\Auth\Models
 */
final class User extends Table {
    protected static $table_name = "auth_user";

    /**
     * @var VarChar
     *
     * Required field of up to 150 characters to store the user's username
     */
    public $username;

    /**
     * @var VarChar
     *
     * Required field for the users password
     */
    public $password;

    /**
     * @var VarChar
     *
     * Optional field to store the users email
     */
    public $email;

    /**
     * @var VarChar
     *
     * Optional field of up to 30 characters to store the users first name
     */
    public $first_name;

    /**
     * @var VarChar
     *
     * Optional field of up to 150 characters to store the users last name
     */
    public $last_name;

    /**
     * @var DateTime
     *
     * Automatically created field storing the date time the user was created
     */
    public $date_joined;

    /**
     * @var DateTime
     *
     * Automatically updated field storing the date time the user was last logged in (active)
     */
    public $last_active;


    /**
     * @var Boolean
     *
     * Boolean field automatically updated storing whether the user is logged in or not
     * True (1) - Logged in
     * False (0) - Logged out
     */
    public $is_active;

    /**
     * @var Boolean
     *
     * Optional boolean field storing whether the user has admin rights or not
     * If true the user can access the admin panel
     * Default - False (0)
     */
    public $is_admin;

    /**
     * @var Boolean
     * Optional boolean field storing whether the user is a superuser or not
     * If true the user has all permissions without having to assign any
     * Default - False (0)
     */
    public $is_superuser;


    /**
     * @var ManyToMany
     *
     * Many to many relationship to 'Permission' storing all the permissions the user has been assigned
     */
    public $permissions;

    /**
     * @var ManyToMany
     *
     * Many to many relationship to 'Group' storing all the groups the user has been assigned
     */
    public $groups;

    public function __construct() {
        parent::__construct();
        $this->username = new VarChar(["not_null" => true, "unique" => true, "length" => 150]);
        $this->password = new VarChar(["not_null" => true, "length" => 200]);
        $this->email = new VarChar();
        $this->first_name = new VarChar(["length" => 30]);
        $this->last_name = new VarChar(["length" => 150]);
        $this->date_joined = new DateTime();
        $this->last_active = new DateTime();

        $this->is_active = new Boolean(["default" => false]);
        $this->is_admin = new Boolean(["default" => false]);
        $this->is_superuser = new Boolean(["default" => false]);

        $this->permissions = new ManyToMany(self::class, Permission::class, "auth_user_permissions");
        $this->groups = new ManyToMany(self::class, Group::class, "auth_user_groups");
    }
}