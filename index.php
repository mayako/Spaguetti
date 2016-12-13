<?php

require 'Spaguetti.php';

class User {
    public $name;

    function __construct(){
        static $i = 1;
        echo 'Setteando '.$i;
        echo '<br>';
        $this->name = 'YOLO';
        $i++;
    }
}

$users = Database::select_sql('SELECT * FROM users')->all();


var_dump($users);
exit();