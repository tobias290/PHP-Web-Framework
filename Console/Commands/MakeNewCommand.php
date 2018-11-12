<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 19/06/2017
 * Time: 21:55
 */

namespace Framework\Console\Commands;


use Framework\Exceptions\FileExistError;

/**
 * Class MakeNewCommand
 * Created a new folder structure for a sub route app
 * @package Framework\Console\Commands
 */
final class MakeNewCommand extends Command {
    protected static $name = "new {name}";
    protected static $description = "Creates a new sub route";

    protected static $help = <<< EOT
Creates a new sub route with all the necessary folders:
    * Controllers - Where all Controllers go
    * Views - where all Views (html files) go

It is also created an 'index_old.php' which is where all the route definitions go

If '--all' flag is applied it also created all the other folders:
    * context_processors - Where as the context mangers for this sub route go
    * Middleware - Where as the Middleware classes for this sub route go
    * Forms - This is where all 'Form' classes go

If the '--with_static' flag is applied it will also create a static file with 3 sub files:
    * css - Where as stylesheets are stored
    * js - Where all javascript files should be stored
    * media - Where all other file are stored such as imaged or logos
EOT;

    public function execute() {
        $file_name = $this->getArg("name");
        $all = $this->hasFlag("all");
        $with_static = $this->hasFlag("with_static");

        if(file_exists("../$file_name"))
            throw new FileExistError("$file_name already exists");

        // Gets the index.pph template from Routing file and insert the file name into the 'Router' constructor
        $index = preg_replace("/{file_name}/", $file_name, file_get_contents("Routing/index_old.php"));

        try {
            // Creates the main directories
            mkdir("../$file_name");
            mkdir("../$file_name/Controllers");
            mkdir("../$file_name/Views");

            // Created the index_old.php
            file_put_contents("../$file_name/index_old.php", $index);

            // If the '--all' flag was applied at the other 2 files
            if ($all) {
                // mkdir("../$file_name/context_processors");
                mkdir("../$file_name/Middleware");
                mkdir("../$file_name/Forms");
            }

            // If the '--with_static' flag as applied create the static file with all the sub-directories
            if ($with_static) {
                mkdir("../$file_name/static");
                mkdir("../$file_name/static/css");
                mkdir("../$file_name/static/js");
                mkdir("../$file_name/static/media");
            }
        } catch (\Exception $e) {
            // Thrown if there is a problem creating any of the directories of files
            echo "Error creating new blog\n";
            echo $e->getMessage();
        }
    }
}