<?php

require 'Spaguetti.php';

Database\Connection::to(['database' => 'ilsafrigo_gastos']);
Database::connect_to('localhost', 'conaquic_guadalajara', 'root');

echo Database\Connection::get_instance()->get_dsn();




// Database::connect([
//     'username' => 'admin',
//     'database' => 'sarao',
//     'charset' => false,
//     'file' => __DIR__ . '/config.php'
// ]);

// header('Content-type: application/json');
// echo json_encode([
//     'config' => Database::$config,
//     'DSN' => Database::get()->get_dsn()
// ]);

// select()