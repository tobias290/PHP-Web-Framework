<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 02/01/2018
 * Time: 18:04
 */

namespace Framework\Console\Commands\Auth;


use Framework\Console\Commands\Command;
use Framework\Security\Auth\Auth;

final class CreateUserCommand extends Command {
    protected static $name = "create:user {username} {password}";
    protected static $description = "Command to create a new user for the Framework\Security\Auth\Models\User table";
    protected static $help = <<< EOT

Create a new user in the 'auth_user' table

Required fields:
    * username - Username for the user (up to 150 characters)
    * password - Password for the user

Optional fields given as flags (e.g. --email, --first_name, etc.):
    * email        - Email for the user
    * first_name   - First name of the user (up to 30 characters)
    * last_name    - Last name of the user (up to 150 characters)
    * is_admin     - Boolean (either true or false), this decides whether the user has admin rights
                     If true the user can access the admin panel
    * is_superuser - Boolean (either true or false), this decides whether the user is a super user
                     If true the user has all permissions without having to assign any
EOT;

    public function execute() {
        $username = $this->getArg("username");
        $password = $this->getArg("password");

        Auth::createUser($username, $password, $this->getFlags());
    }

}