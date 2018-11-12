<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 31/12/2017
 * Time: 12:42
 */

namespace Framework\Security\Auth;

use Framework\Database\{DataEntity, DB, Table};
use Framework\Http\Routing\Router;
use Framework\Security\Auth\Models\Permission;

/**
 * Class AuthUser
    * Represents the user model once he is authenticated and logged in
 * @package Framework\Security\Auth
 */
final class AuthUser {
    /**
     * @var DataEntity
     *
     * Represents the authenticated user
     */
    private $auth_user;

    /**
     * @var Router
     *
     * Current router instance
     */
    private $router;

    /**
     * AuthUser constructor.
     * @param $user
     * @param Router $router - Current router instance
     */
    public function __construct($user, $router) {
        $this->auth_user = $user;

        $this->router = $router;
    }

    /**
     * @return DataEntity - Returns the users data entity
     */
    public function getDataEntity() {
        return $this->auth_user;
    }

    /**
     * @return string - Returns the authenticated user's id
     */
    public function getId() {
        return $this->auth_user->id;
    }

    /**
     * @return string - Returns the authenticated user's username
     */
    public function getUsername() {
        return $this->auth_user->username;
    }

    /**
     * @return string - Returns the authenticated user's password
     */
    public function getPassword() {
        return $this->auth_user->password;
    }

    /**
     * @return string - Returns the authenticated user's email
     */
    public function getEmail() {
        return $this->auth_user->email;
    }

    /**
     * @return string - Returns the authenticated user's first_name
     */
    public function getFirstName() {
        return $this->auth_user->first_name;
    }

    /**
     * @return string - Returns the authenticated user's last_name
     */
    public function getLastName() {
        return $this->auth_user->last_name;
    }

    /**
     * @return string - Returns the authenticated user's date_joined
     */
    public function getDateJoined() {
        return $this->auth_user->date_joined;
    }

    /**
     * @return string - Returns the authenticated user's last_active
     */
    public function getLastActive() {
        return $this->auth_user->last_active;
    }

    /**
     * @return boolean - Returns the authenticated user's is_active
     */
    public function isActive() {
        return $this->auth_user->is_active;
    }

    /**
     * @return boolean - Returns the authenticated user's is_admin
     */
    public function isAdmin() {
        return $this->auth_user->is_admin;
    }

    /**
     * @return boolean - Returns the authenticated user's is_superuser
     */
    public function isSuperuser() {
        return $this->auth_user->is_superuser;
    }

    /**
     * @return array - Returns the authenticated user's permissions
     */
    public function getPermissions() {
        return $this->auth_user->permissions;
    }

    /**
     * @return array - Returns the authenticated user's groups
     */
    public function getGroups() {
        return $this->auth_user->groups;
    }

    public function isAuthenticated() {
        return true;
    }

    /**
     * Checks whether the user has permission for a certain route
     *
     * @param string $permission - Permission to check for
     * @return bool - Returns true if the user has the given permission, false if not
     * @throws \Framework\Exceptions\MultipleResultsError
     * @throws \Framework\Exceptions\SQLQueryError
     */
    public function hasPermission($permission) {
        $table = DB::getAuthUserTable();

        foreach ($table->get("id = ?", [$this->auth_user->id])->permissions as $pm) {
            if ($permission == $pm) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds one or more permissions for the user
     *
     * @param array ...$permissions - Array of route names to add to the permissions table
     */
    public function addPermissions(...$permissions) {
        $table = DB::getAuthUserTable();
    }

    /**
     * Remove one or more permissions for the user
     *
     * @param array ...$permissions - Array of route names to remove from the permissions table
     */
    public function removePermissions(...$permissions) {
        // TODO: implement
    }

    /**
     * Clears all of the users permissions from the permissions table
     */
    public function clearPermissions() {
        // TODO: implement
    }

    /**
     * Add one or more permission groups for the user
     *
     * @param array ...$permission_groups - Array of permission group names to remove from the permissions table
     */
    public function addPermissionGroups(...$permission_groups) {
        // TODO: implement
    }

    /**
     * Remove one or more permission groups for the user
     * @param array ...$permission_groups - Array of permission group names to remove from the permissions table
     */
    public function removePermissionGroups(...$permission_groups) {
        // TODO: implement
    }

    /**
     * Clears the all of the users permission groups from the group table
     */
    public function clearPermissionGroups() {
        // TODO: implement
    }
}