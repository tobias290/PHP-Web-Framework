<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 29/06/2017
 * Time: 10:57
 */

namespace Framework\Console\Commands\Database;


use Framework\Console\Commands\Command;
use Framework\Database\DB;
use Framework\Exceptions\SQLQueryError;

final class DropTableCommand extends Command {
    protected static $name = "drop {table}";
    protected static $description = "Drops the table";
    protected static $help = "Deletes the table from the database";

    public function execute() {
        $table = $this->getArg("table");

        $sql = "DROP $table";

        $q = DB::connection()->query($sql);

        if($q === false)
            //throw new ColumnNotFoundError("Error given column(s) do not exist in table");
            throw new SQLQueryError(DB::connection()->error());
        else {
            echo "$table was successfully dropped";
        }
    }
}