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
      'user' => 'user',
      'pass' => 'password',
      'dbname' => 'xxx',
  ]
];