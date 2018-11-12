<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 17/06/2017
 * Time: 19:00
 */

namespace Framework\Console\Commands\Database;

use Framework\Console\Commands\Command;
use Framework\Database\Connections\ResultTypes;
use Framework\Database\DB;
use Framework\Exceptions\SQLQueryError;
use Framework\Helpers\StringFunctions;

final class SQLQueryCommand extends Command {
    use StringFunctions;

    protected static $name = "query {sql}";
    protected static $description = "Runs any SQL code and either returns the result or exits if no result";
    protected static $help = "Runs any SQL code and either returns the result or exits if no result";

    public function execute() {
        $sql = $this->getArg("sql");

        $q = DB::connection()->query($sql);

        if($q === false)
            //throw new ColumnNotFoundError("Error given column(s) do not exist in table");
            throw new SQLQueryError(DB::connection()->error());
        elseif(DB::connection()->num_rows($q) > 0)
            $result = DB::connection()->fetch_all($q, ResultTypes::ASSOC);
        else
            $result = null;

        // This occurs if the user is entering data not retrieving data
        if($result == null) exit;

        if($this->hasFlag("raw")) {
            print_r($result);
            exit;
        }

        $data = [];

        foreach (array_keys($result[0]) as $i => $header) {
            $array = [];
            foreach ($result as $k => $value) {
                array_push($array, $value[$header]);
            }
            $data[$header] = $array;
        }

        if(count($data) == 1) {
            $header = array_keys($data)[0];
            $this->stringTable($data[$header], $header);
        } else {
            $this->multiColumnStringTable($data, count($result));
        }
    }
}