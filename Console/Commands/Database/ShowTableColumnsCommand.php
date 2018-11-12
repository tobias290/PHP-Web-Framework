<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 17/06/2017
 * Time: 17:05
 */

namespace Framework\Console\Commands\Database;


use Framework\Console\Commands\Command;
use Framework\Database\{DB, Table};
use Framework\Exceptions\TableNotFound;
use Framework\Helpers\StringFunctions;

final class ShowTableColumnsCommand extends Command {
    // Using 'stringTable()'
    use StringFunctions;

    protected static $name = "show:columns {table}";
    protected static $description = "Lists all the columns in a specific table";
    protected static $help = "Lists all the columns in a specific table";

    public function execute() {
        /** @var Table $table */
        try {
            $columns = (DB::table($this->getArg("table")))->showColumns();
        } catch (TableNotFound $e) {
            return print "That table does not exist";
        }

        $data = [];

        foreach (array_keys($columns[0]) as $i => $header) {
            $array = [];
            foreach ($columns as $k => $value) {
                array_push($array, $value[$header]);
            }
            $data[$header] = $array;
        }

        $this->multiColumnStringTable($data, count($columns));
    }
}