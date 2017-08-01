<?php

require 'vendor/autoload.php';

$app = new \Slim\App();

echo 1;
$app->get('/index', function ($req, $res, $args) {
    $response->write("aaa");
});
echo 2;

$app->run();
