<?php

require 'vendor/autoload.php';
require 'app/function.php';
require 'app/constant/env.php';
require 'app/constant/userRole.php';

// 不写类型没有代码提示...
use Slim\Http\Request;
use Slim\Http\Response;

// 日志库
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BufferHandler;

use Ramsey\Uuid\Uuid;
use Underscore\Types\Strings;
use App\Component\AggregateStreamHandler;

// 预设环境
date_default_timezone_set('asia/shanghai');

// 从源头远离编码问题...
mb_internal_encoding('UTF-8');
mb_http_input('UTF-8');
mb_http_output('UTF-8');
mb_regex_encoding('UTF-8');

// 定义环境
define('ENV', App\Constant\Env::ENV_DEV);

// 定义常用目录
define('DIR_ROOT', dirname(__DIR__));
define('DIR_PUBLIC', DIR_ROOT . '/public');
define('DIR_APP', DIR_ROOT . '/app');

// 请求开始的时间戳
define('TIME_REQUEST_START', get_timestamp());

// 为每个请求分配一个唯一 ID
// 请求内部其他服务时，带上这个 request id，这样便可以把一次请求串起来，方便排查问题
define('ID_REQUEST', Uuid::uuid1()->getHex());

$app = new \Slim\App([
    'settings' => require_file_by_env('app/config/config.php')
]);

$container = $app->getContainer();
$container['errorHandler'] = function ($container) {
    return function ($request, $response, Exception $exception) use ($container) {
        $container->get('logger')->error($exception->getMessage());
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    };
};
$container['logger'] = function ($container) {

    $request = $container->get('request');

    $settings = $container->get('settings')['logger'];

    // 以日期为目录
    // 每一天的目录下按小时拆分文件
    $path = $settings['dir'] . DIRECTORY_SEPARATOR
        . ENV . DIRECTORY_SEPARATOR
        . date('Y-m-d') . DIRECTORY_SEPARATOR
        . date('H') . '.log';

    $logger = new Logger($settings['name']);

    $handler = new AggregateStreamHandler($path, $settings['level'], $request);

    $handler->setFormatter(
        new LineFormatter("           [%datetime%][%level_name%] %message% %context% %extra%\n", 'H:i:s', true, true)
    );

    $logger->pushHandler(new BufferHandler($handler));

    return $logger;

};

$app->add(new RKA\Middleware\IpAddress(true, ['10.0.0.1', '10.0.0.2']));

$app->add(function (Request $request, Response $response, Callable $next) {

    $this->logger->info('Request Start', $request->getParams());

    // 方便 Nginx 日志和 php 日志串起来
    $response = $response->withHeader('Request-Id', ID_REQUEST);

    $path = $request->getUri()->getPath();

    // 所有 Action 都位于 app/action 目录下
    $terms = [ 'App', 'Action' ];

    // 默认主页
    if ($path === '/') {
        $terms[] = 'Index';
    }
    // 按 path 结构映射本地目录结构
    else {
        foreach (explode('/', $path) as $term) {
            $term = trim($term);
            if ($term) {
                $terms[] = ucfirst(Strings::toCamelCase($term));
            }
        }
    }

    // 所有 Action 文件名以 Action 结尾
    $ActionClass = implode('\\', $terms) . 'Action';

    if (class_exists($ActionClass)) {
        // 一个请求映射一个 Action
        // 通常 Action 是非常薄的一层，只用于权限、参数校验，业务逻辑最终通过调用 service 层实现
        $action = new $ActionClass($request, $response);
        // 处理完之后，获取改写后的 response
        $response = $action->execute();
    }
    else {
        $this->logger->notice('Action not found');
        $response = $response->withStatus(404, 'Not Found');
    }

    // 正常结束的请求会打印 request end
    $this->logger->info('Request End');

    return $next($request, $response);

});

$app->run();

