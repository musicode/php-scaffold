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
      'host' => 'localhost',
      'port' => 3306,
      'username' => 'root',
      'password' => '',
      'name' => 'jrd',
  ]
];