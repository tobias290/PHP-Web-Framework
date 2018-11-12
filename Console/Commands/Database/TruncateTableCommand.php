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

final class TruncateTableCommand extends Command {
    protected static $name = "truncate {table}";
    protected static $description = "Deletes all the content of a given table";
    protected static $help = "Deletes all the content of a given table. \n* To reset auto increment add flag '--reset_counter'";

    public function execute() {
        $table = $this->getArg("table");

        if($this->hasFlag("reset_counter"))
            $sql = "TRUNCATE $table; ALTER TABLE $table AUTO_INCREMENT = 1";
        else
            $sql = "TRUNCATE $table";

        $q = DB::connection()->query($sql);

        if($q === false)
            //throw new ColumnNotFoundError("Error given column(s) do not exist in table");
            throw new SQLQueryError(DB::connection()->error());
        else {
            echo "Contents of $table successfully deleted";
        }
    }
}