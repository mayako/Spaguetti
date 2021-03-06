<?php

/**
 * Valid if an array is multiple
 * @param  array   $arr
 * @return bool
 */
function is_multiple(array $array)
{
    return (bool) array_filter($array, 'is_array');
}

/**
 * Valid if an array is associative
 * @param  array   $arr
 * @return bool
 */
function is_assoc(array $array)
{
    return (bool) array_first($array, 'is_string', ARRAY_FIRST_USE_KEY);
}

/**
 * Alias of "array_map", but this add the keys to the callback by default
 * @param  array  $arr
 * @param  Closure $callback
 * @return array
 */
function array_map_with_keys(array $arr, $callback)
{
    return array_map($callback, $arr , array_keys($arr));
}

/**
 * Get all values of an array recursively keeping keys
 * @param  array  $arr
 * @return array
 */
function array_values_recursive_with_keys(array $array)
{
    $rs = array();
    array_walk_recursive($array, function($value, $key) use (&$rs) {
        $rs[$key] = $value;
    });

    return $rs;
}

/**
 * Get all values of an array recursively
 * @param  array  $arr
 * @return array
 */
function array_values_recursive($array)
{
    $rs = array();

    foreach($array as $value) {
        if (is_array($value)) {
            $rs = array_merge($rs, array_values_recursive($value));
        }
        else {
            $rs[] = $value;
        }
    }
    return $rs;
}

/**
 * Return the first value of an array that fulfill a condition
 * @param  array  $arr
 * @param  Closure $callback
 * @return mixed
 */
define('ARRAY_FIRST_USE_VALUE', 1);
define('ARRAY_FIRST_USE_KEY', 2);
define('ARRAY_FIRST_USE_BOTH', 3);
define('ARRAY_FIRST_USE_BOTH_INVERSE', 4);

function array_first(array $array, $callback = null, $flag = ARRAY_FIRST_USE_BOTH)
{
    if (!$callback) {
        return reset($array);
    }

    foreach ($array as $key => $value) {

        switch ($flag) {
            case ARRAY_FIRST_USE_VALUE:
                $bool = call_user_func($callback, $value);
                break;

            case ARRAY_FIRST_USE_KEY:
                $bool = call_user_func($callback, $key);
                break;

            case ARRAY_FIRST_USE_BOTH_INVERSE:
                $bool = call_user_func($callback, $key, $value);
                break;

            default:
                $bool = call_user_func($callback, $value, $key);
                break;
        }

        if ($bool) {
            return $value;
        }
    }

    return;
}

/**
 * Take an element of an array
 * @param  array  &$arr
 * @param  mixed $key
 * @return mixed
 */
function array_take(array &$array, $key)
{
    if (!isset($array[$key])) {
        return false;
    }

    $value = $array[$key];
    unset($array[$key]);

    return $value;
}

/**
 * Get an element of an array
 * @param  array  $array
 * @param  mixed $key
 * @param  mixed $default
 * @return mixed
 */
function array_get(array $array, $key, $default = false)
{
    if (empty($array[$key])) {
        return $default;
    }

    return $array[$key];
}

/**
 * Return an array $key => $value of an array multiple
 * @param  array   $arr
 * @param  string  $value
 * @param  string  $key
 * @param  bool $collapse
 * @return array
 */
function array_pluck(array $arr, $value, $key = null, $collapse = false)
{
    $rows = array();
    foreach ($arr as $row) {
        $row = (array) $row;

        if ($key == null) {
            $rows[] = $row[$value];
            continue;
        }

        $_val = $row[$value];
        $_key = $row[$key];

        if ($collapse && $rows[$_key]) {

            $rows[$_key] = array_merge((array) $rows[$_key], (array) $_val);

            continue;
        }


        $rows[$_key] = $_val;
    }

    return $rows;
}

/**
 * Add an element to the array
 * @param  array  &$arr
 * @param  mixed $value
 * @return array
 */
function array_add(array &$arr, $value)
{
    $arr = array_merge($arr, (array) $value);
}

/**
 * Return the original array excluding the values
 * @param  array  $array
 * @param  array  $values
 * @return array
 */
function array_except(array $array, $values)
{
    return array_diff($array, $values);
}

/**
 * Return the original array excluding the keys
 * @param  array  $array
 * @param  array  $keys
 * @return array
 */
function array_except_keys(array $array, $keys)
{
    return array_diff_key($array, array_flip((array) $keys));
}

/**
 * Return only the values from the original array
 * @param  array  $array
 * @param  array  $values
 * @return array
 */
function array_only(array $array, $values)
{
    return array_intersect($array, (array) $values);
}

/**
 * Return only the keys from the original array
 * @param  array  $array
 * @param  array  $keys
 * @return array
 */
function array_only_keys(array $array, $keys)
{
    return array_intersect_key($array, array_flip((array) $keys));
}

/**
 * Rename keys of array with an array old_key => new_key
 * @param  array  $array
 * @param  array  $new_keys
 * @return array
 */
function array_rename_keys(array &$array, array $new_keys)
{
    foreach ($new_keys as $old_key => $new_key) {

        if (isset($array[$old_key])) {

            $array[$new_key] = $array[$old_key];

            unset($array[$old_key]);
        }
    }

    return $array;
}

/**
 * Group rows in array with keys based en string
 * @param  array  $array
 * @param  string $dot
 * @return array
 */
function array_group(array $array, $dot)
{
    $rs = array();

    foreach ($array as $value) {

        $_value = (array) $value;

        $exp = '$rs';

        foreach (explode('.', $dot) as $node) {
            $exp .= '['.$_value[$node].']';
        }

        $exp .= '[] = $value;';

        eval($exp);
    }

    return $rs;
}