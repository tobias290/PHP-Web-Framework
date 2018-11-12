<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 04/01/2018
 * Time: 13:11
 */

namespace Framework\Console\Commands\Auth;


use Framework\Console\Commands\Command;
use Framework\Database\DB;

final class CreatePermissionCommand extends Command {
    protected static $name = "create:permission {name} {code_name}";
    protected static $description = "Command to create a new permission for the Framework\Security\Auth\Models\User table";
    protected static $help = <<< EOT

Create a new permission in the 'auth_permission' table

Required parameters:
    * name - Name for the permission (up to 100 characters) (E.g. 'Can Edit') 
             For this to work enclose name in ""
    * code_name - Code name for the permission to use to reference it (up to 100 characters)(E.g. 'can_edit')
EOT;

    public function execute() {
        $maker = DB::getAuthPermissionTable()->new();

        $maker->name = $this->getArg("name");
        $maker->code_name = $this->getArg("code_name");
        $maker->save();
    }

}