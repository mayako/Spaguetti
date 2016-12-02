<?php

if (!defined('SPAGUETTI_ROOT')) {
    define('SPAGUETTI_ROOT', __DIR__);
}

if (!defined('SPAGUETTI_CLASSES_PATH')) {
    define('SPAGUETTI_CLASSES_PATH', SPAGUETTI_ROOT . DIRECTORY_SEPARATOR . 'Classes');
}

if (!defined('SPAGUETTI_HELPERS_PATH')) {
    define('SPAGUETTI_HELPERS_PATH', SPAGUETTI_ROOT . DIRECTORY_SEPARATOR . 'Helpers');
}


require SPAGUETTI_CLASSES_PATH . DIRECTORY_SEPARATOR . 'autoload.php';
require SPAGUETTI_HELPERS_PATH . DIRECTORY_SEPARATOR . 'autoload.php';
