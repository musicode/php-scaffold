<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require 'app/function.php';

use Ramsey\Uuid\Uuid;
use Underscore\Types\Strings;

// 预设环境
date_default_timezone_set('asia/shanghai');

// 从源头远离编码问题...
mb_internal_encoding('UTF-8');
mb_http_input('UTF-8');
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8');

// 定义环境
define('ENV_DEV', 'dev');
define('ENV_TEST', 'test');
define('ENV_PROD', 'prod');
define('ENV', ENV_DEV);

// 定义常用目录
define('DIR_ROOT', dirname(__DIR__));
define('DIR_PUBLIC', DIR_ROOT . '/public');
define('DIR_APP', DIR_ROOT . '/app');

// 请求开始的时间戳
define('TIME_REQUEST_START', microtime(true));

// 为每个请求分配一个唯一 ID
// 请求内部其他服务时，带上这个 request id，这样便可以把一次请求串起来，方便排查问题
define('ID_REQUEST', Uuid::uuid1()->getHex());

$settings = require_file_by_env('app/config/config.php');

$app = new \Slim\App([
    'settings' => $settings
]);

$container = $app->getContainer();
$container['errorHandler'] = function ($container) {
    return function ($request, $response, $exception) {
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    };
};
$container['logger'] = function ($container) {
    $settings = $container->get('settings')['logger'];

    // 以日期为目录
    // 每一天的目录下按小时拆分文件
    $filePath = $settings['dir'] . DIRECTORY_SEPARATOR
        . ENV . DIRECTORY_SEPARATOR
        . date('Y-m-d') . DIRECTORY_SEPARATOR
        . date('H') . '.log';

    $logger = new Monolog\Logger($settings['name']);
    $fileHandler = new Monolog\Handler\BufferHandler($filePath);
    $logger->pushHandler($fileHandler);

    return $logger;
};

$app->add(function (Request $request, Response $response, Callable $next) {

    // 方便 Nginx 日志和 php 日志串起来
    $response = $response->withHeader('Request-Id', ID_REQUEST);

    $path = $request->getUri()->getPath();

    // App\Action 根目录
    $terms = [ 'App', 'Action' ];
    foreach (explode('/', $path) as $term) {
        $term = trim($term);
        if ($term) {
            $terms[] = ucfirst(Strings::toCamelCase($term));
        }
    }

    // 所有 Action 文件名以 Action 结尾
    $ActionClass = implode('\\', $terms) . 'Action';

    $this->logger->info($ActionClass);

    if (class_exists($ActionClass)) {
        $instance = new $ActionClass($request, $response);
    }
    else {
        $response = $response->withStatus(404, 'Not Found');
    }

    return $next($request, $response);

});

$app->run();
