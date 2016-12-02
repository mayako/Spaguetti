<?php


if (!function_exists('__autoload')) {
    function __autoload($class)
    {
        if (class_exists($class, false)) {
            return false;
        }

        $filename = SPAGUETTI_CLASSES_PATH
                . DIRECTORY_SEPARATOR
                . $class
                . '.php';

        echo $filename;
        echo '<br>';

        if (!file_exists($filename) && !is_readable($filename)) {
            return false;
        }

        require $filename;
    }
}