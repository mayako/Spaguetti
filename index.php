<?php

require 'Spaguetti.php';


Database::connect_to('localhost', 'test', 'root');

// $users = Database::query('SELECT * FROM users')->as_assoc()->fetch(function($user){
//     $user['name'] = strtoupper($user['name']);
//     return $user;
// });



$users = Database::query('SELECT * FROM users')->to_sql();


echo $users;