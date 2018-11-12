<?php
/**
 * Created by PhpStorm.
 * User: toby
 * Date: 12/12/2017
 * Time: 19:26
 */

namespace Framework\Security\Auth;


use Framework\Database\{DataEntity, DB};
use Framework\Exceptions\{
    UserCreationError, DetailNotAllowedException, NoAuthenticatedUserError, SessionVariableNotFound
};
use Framework\Http\Requests\Request;
use Framework\Http\Routing\Router;
use Framework\Security\Hash;

/**
 * Class Auth
    * Class used to authenticate users, log in/out authenticated users
 * @package Framework\Security\Auth
 */
final class Auth {
    /**
     * @var Router
     *
     * Router instance
     */
    private static $router;

    private function __construct() {}

    /**
     * Give the Auth user a router instance so it can check the current route for information
     * @param Router $router - Current router instance
     */
    public static function setRouter($router) {
        self::$router = $router;
    }

    /**
     * Method used to create a new user
     *
     * @param string $username - Username for the user
     * @param string $password - User's password
     * @param array $extra_details - Associate list with any extra fields which are optional (e.g. ["email" => "example@test.com"])
     * @throws UserCreationError - Thrown if the username or password is blank while creating a new auth user
     * @throws DetailNotAllowedException - Thrown in the user tries to save an extra detail that isn't allowed (e.g. is_active, last_active)
     * @throws \Framework\Exceptions\HashNotCreatedError - Thrown if the program fails to create a hash
     */
    public static function createUser($username, $password, $extra_details=[]) {
        // TODO: hash password

        if(empty($username) or empty($password)) {
            throw new UserCreationError("Username and password cannot be empty");
        }

        // Create a new entity maker for the auth user table
        $maker = DB::getAuthUserTable()->new();

        // Set the required details
        $maker->username = $username;
        $maker->password = Hash::make($password);
        $maker->date_joined = date("Y-m-d H:i:s");

        // Now set any extra details supplied by the user
        foreach ($extra_details as $detail => $value) {
            if($detail == "is_active" or $detail == "last_active" or $detail == "date_joined") {
                throw new DetailNotAllowedException("Cannot set $detail");
            } else if (DB::getAuthUserTable()->hasField($detail)) {
                $maker->{$detail} = $value;
            } else {
                throw new UserCreationError("$detail field does not exist in the User ('auth_user') table");
            }
        }

        // Save the new user
        $maker->save();
    }

    /**
     * Check to see if a user exists with given details
     *
     * @param string $username - Username to check for  in Database
     * @param string $password - Password to check for in Database
     * @return \Framework\Database\DataEntity | null - Either returns the user object or if the user doesn't exist it returns null
     * @throws \Framework\Exceptions\MultipleResultsError - Thrown if Tables 'get()' method returns more than one result
     * @throws \Framework\Exceptions\SQLQueryError - Thrown if there is an error the SQL query
     */
    public static function authenticate($username, $password) {
        $auth_user_table = DB::getAuthUserTable();

        $user = $auth_user_table->get("username = ?", [$username]);

        return ($user->isEmpty() or !Hash::verify($password, $user->password)) ? null : $user;
    }

    /**
     * Logs in the authenticated user
     *
     * @param Request $request
     * @param DataEntity $user
     */
    public static function login($request, $user) {
        $user->is_active = true;
        $user->last_active = date("Y-m-d H:i:s");

        $request->session->setAuthUser(new AuthUser($user, self::$router));
    }

    /**
     * Logs out the authenticated user
     *
     * @param Request $request
     * @throws NoAuthenticatedUserError
     * @throws SessionVariableNotFound
     */
    public static function logout($request) {
        if(is_null($request->user)) throw new NoAuthenticatedUserError("Can logout when a user hasn't been logged in");

        $request->user->getDataEntity()->is_active = 0;
        $request->session->unset("auth_user");
    }

    /**
     * Checks to see if a user is logged in or not
     *
     * @param Request $request - Request to search for user in
     * @return bool - Returns true if a user is logged in or false if not
     */
    public function check($request) {
        return empty($request->user) ? false : true;
    }

    /**
     * A shortcut function - If the current logged in user is not authenticated, this will redirect to the given route
     *
     * @param Request $request - Request passed so we can redirect the user
     * @param string $route_name - Name of the route to redirect the user to
     * @param array $params - Parameters for the route to redirect to - Can be empty
     */
    public static function redirectUserIfNotAuthenticated($request, $route_name, $params=[]) {
        $is_authenticated = $request->user->isAuthenticated();

        if (!$is_authenticated)
            self::$router->redirect($route_name, $params);
    }

    /**
     * Returns the current authenticated user
     *
     * @return AuthUser|null - Returns the AuthUser instance or null if no user is authenticated
     */
    public static function user() {
        return $_SESSION["auth_user"] ?? null;
    }
}