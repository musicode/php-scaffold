<?php

return [
    'displayErrorDetails' => true,
    'addContentLengthHeader' => false,

    'logger' => [
        'name' => 'my_logger',
        'level' => Monolog\Logger::DEBUG,
        'dir' => DIR_ROOT . '/log'
    ],

    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => 'root',
        'password' => '',
        'dbname' => 'jrd',
        'charset' => 'utf8mb4',
    ],

    'redis' => [
        'host' => '127.0.0.1',
        'port' => '6379',
        'password' => '',
    ]
];