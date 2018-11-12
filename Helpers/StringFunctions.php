<?php
/**
 * Created by PhpStorm.
 * User: tobys
 * Date: 17/06/2017
 * Time: 17:00
 */

namespace Framework\Helpers;

/**
 * Trait StringFunctions
    * Holds various reusable string functions
 * @package Framework\Traits
 */
trait StringFunctions {
    use ArrayFunctions;

    /**
     * Displays a table of rows
     * @param array $rows - Array of rows to display
     * @param string $title - Title to display at top of table
     */
    private function stringTable($rows, $title) {
        array_push($rows, $title);

        $max = max(array_map("strlen", ($rows)));

        echo "+" . str_repeat("-", ($max + 2)) . "+\n";
        echo "| $title" . str_repeat(" ", $max - strlen(" $title") + 2) . "|\n";
        echo "+" . str_repeat("-", ($max + 2)) . "+\n";


        foreach ($rows as $row) {
            if ($row == $title) continue;
            echo "| $row" . str_repeat(" ", $max - strlen(" $row") + 2) . "|\n";
        }

        echo "+" . str_repeat("-", ($max + 2)) . "+\n";
    }

    /**
     * Function for a multi-column table
     * @param array $data - Nested associative array with array keys representing headers and value array as columns
     * @param int $data_count - How many results there are
     */
    private function multiColumnStringTable($data, $data_count) {
        $maxes = [];
        $max = 0;

        foreach (array_keys($data) as $i => $header) {
            $new = max(array_map("strlen", $data[$header]));
            if ($new > $max) $max = $new;
            if ($header > $max) $max = $header;

            $maxes[$i] = $max;
        }

        $header_max = max(array_map("strlen", array_keys($data)));
        if($header_max > $max) $max = $header_max;

        $header_count = count(array_keys($data));

        for ($i = 0; $i < $header_count; $i++) {
            echo "+" . str_repeat("-", ($maxes[$i] + 2));
        }
        echo "+\n";
        foreach (array_keys($data) as $i => $header) {
            echo "| $header" . str_repeat(" ", $maxes[$i] - strlen(" $header") + 2);
        }
        echo "|\n";
        for ($i = 0; $i < $header_count; $i++) {
            echo "+" . str_repeat("-", ($maxes[$i] + 2));
        }
        echo "+\n";
        for ($i = 0; $i < $data_count; $i++) {
            $array = $this->arrayValueRecursive($i, $data);

            foreach ($array as $k => $value) {
                echo "| $value" . str_repeat(" ", $maxes[$k] - strlen(" $value") + 2);
            }
            echo "|\n";
        }
        for ($i = 0; $i < $header_count; $i++) {
            echo "+" . str_repeat("-", ($maxes[$i] + 2));
        }
        echo "+";
    }
}