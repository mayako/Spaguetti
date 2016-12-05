<?php


foreach (glob(SPAGUETTI_HELPERS_PATH . DIRECTORY_SEPARATOR . '*.php') as $filename)
{
    if ($filename !== __FILE__) {
        include $filename;
    }
}