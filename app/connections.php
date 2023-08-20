<?php

return [
    'db' => [
        'driver' => 'pdo_mysql',
        'host' => env('DB_HOST'),
        'port' => 3306,
        'dbname' => env('DB_NAME'),
        'user' => env('DB_USER'),
        'password' => env('DB_PASSWORD'),
        'charset' => 'utf8',
        'driverOptions' => [
            1002 => 'SET NAMES utf8'
        ]
    ]
];