<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 14/06/2017
 * Time: 22:12
 */

namespace Framework\Console\Commands;

/**
 * Class StartServer
    * Starts the development server
 * @package Framework\Console\Commands
 */
final class StartServer extends Command {
    protected static $name = "start {socket=localhost:90}";
    protected static $description = "Starts the builtin development server";
    protected static $help = "* Starts the development server at 'localhost:90'. \n* To start on a different socket add argument after start command";

    public function execute() {
        $_ = explode("\\", __DIR__);
        $_ = array_splice($_, 0, -3);
        $dir = implode("\\", $_);

        echo "Running on {$this->getArg('socket')} | CTRL-C to exit. \n";
        exec("\"" . PHP_BINARY . "\"" . " -S ". $this->getArg("socket") . " \"" . $dir . "\server.php\"");
    }
}