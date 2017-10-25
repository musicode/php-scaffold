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
        'host' => 'mysql',
        'port' => 3306,
        'username' => 'default',
        'password' => 'secret',
        'dbname' => 'default',
        'charset' => 'utf8mb4',
    ],

    'redis' => [
        'host' => 'redis',
        'port' => '6379',
        'password' => '',
    ],

    'view' => [
        'template_dir' => DIR_APP . '/view',
        'compile_dir' => DIR_ROOT . '/compile',
        'cache_dir' => DIR_ROOT . '/cache',
    ]
];
