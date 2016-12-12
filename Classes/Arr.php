<?php

// define('ARRAY_FIRST_USE_VALUE', 1);
// define('ARRAY_FIRST_USE_KEY', 2);
// define('ARRAY_FIRST_USE_BOTH', 3);
// define('ARRAY_FIRST_USE_BOTH_INVERSE', 4);

class Arr
{

    const FIRST_USE_VALUE        = 1;
    const FIRST_USE_KEY          = 2;
    const FIRST_USE_BOTH         = 3;
    const FIRST_USE_BOTH_INVERSE = 4;

    /**
     * Valid if an array is multiple
     * @param  array   $arr
     * @return bool
     */
    public static function is_multiple(array $array)
    {
        return (bool) Arr::first($array, 'is_array', Arr::FIRST_USE_VALUE);
    }

    /**
     * Valid if an array is associative
     * @param  array   $arr
     * @return bool
     */
    public static function is_assoc(array $array)
    {
        return (bool) Arr::first($array, 'is_string', Arr::FIRST_USE_KEY);
    }


    /**
     * Return the first value of an array that fulfill a condition
     * @param  array  $arr
     * @param  Closure $callback
     * @return mixed
     */
    public static function first(array $array, $callback = null, $flag = Arr::FIRST_USE_BOTH)
    {
        if (!$callback) {
            return reset($array);
        }

        foreach ($array as $key => $value) {

            switch ($flag) {
                case Arr::FIRST_USE_VALUE:
                    $bool = call_user_func($callback, $value);
                    break;

                case Arr::FIRST_USE_KEY:
                    $bool = call_user_func($callback, $key);
                    break;

                case Arr::FIRST_USE_BOTH_INVERSE:
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
}