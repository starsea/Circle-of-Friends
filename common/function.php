<?php

use Pyramid\Component\Database\Database;
use Pyramid\Component\Database\Condition;

if (!function_exists('array_column')) {
    function array_column($input, $column_key, $index_key = null)
    {
        if ($index_key !== null) {
            // Collect the keys
            $keys = array();
            $i    = 0; // Counter for numerical keys when key does not exist

            foreach ($input as $row) {
                if (array_key_exists($index_key, $row)) {
                    // Update counter for numerical keys
                    if (is_numeric($row[$index_key]) || is_bool($row[$index_key])) {
                        $i = max($i, (int)$row[$index_key] + 1);
                    }

                    // Get the key from a single column of the array
                    $keys[] = $row[$index_key];
                } else {
                    // The key does not exist, use numerical indexing
                    $keys[] = $i++;
                }
            }
        }

        if ($column_key !== null) {
            // Collect the values
            $values = array();
            $i      = 0; // Counter for removing keys

            foreach ($input as $row) {
                if (array_key_exists($column_key, $row)) {
                    // Get the values from a single column of the input array
                    $values[] = $row[$column_key];
                    $i++;
                } elseif (isset($keys)) {
                    // Values does not exist, also drop the key for it
                    array_splice($keys, $i, 1);
                }
            }
        } else {
            // Get the full arrays
            $values = array_values($input);
        }

        if ($index_key !== null) {
            return array_combine($keys, $values);
        }

        return $values;
    }
}

//把 json 构成的数组转换成纯粹的 array
function json2Array(Array $arr)
{
    $ret = array();
    if ($arr) {
        foreach ($arr as $v) {
            $ret[] = json_decode($v, true);
        }

    }
    return $ret;
}

/**
 * @usage
 *
 * db_select('table', 'alias')
 *   ->fields('alias')
 *   ->condition('id', 1)
 *   ->execute()
 *   ->fetchAssoc();
 */
function db_select($table, $alias = null, array $options = array()) {
    if (empty($options['target'])) {
        $options['target'] = 'master';
    }
    return Database::getConnection($options['target'])->select($table, $alias, $options);
}


/**
 * @usage
 *
 * db_insert('table')
 *   ->fields(array(
 *      'name' => 'value',
 *   ))
 *   ->execute();
 */
function db_insert($table, array $options = array()) {
    if (empty($options['target']) || $options['target'] == 'slave') {
        $options['target'] = 'master';
    }
    return Database::getConnection($options['target'])->insert($table, $options);
}


