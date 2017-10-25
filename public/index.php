<?php

require '../vendor/autoload.php';
require '../app/function.php';

// 不写类型没有代码提示...
use Slim\Http\Request;
use Slim\Http\Response;

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
define('ENV', App\Constant\Env::DEV);

// 定义常用目录
define('DS', DIRECTORY_SEPARATOR);
define('DIR_ROOT', dirname(__DIR__));
define('DIR_PUBLIC', DIR_ROOT . '/public');
define('DIR_APP', DIR_ROOT . '/app');

// 请求开始的时间戳
define('TIME_REQUEST_START', get_timestamp());

// 为每个请求分配一个唯一 ID
// 请求内部其他服务时，带上这个 request id，这样便可以把一次请求串起来，方便排查问题
define('ID_REQUEST', Uuid::uuid1()->getHex());

$app = new \Slim\App([
    'settings' => require_file_by_env(DIR_ROOT . '/app/config/config.php')
]);

$container = $app->getContainer();

$services = require DIR_APP . '/service.php';
foreach ($services as $key => $value) {
  $container[ $key ] = $value;
}

$app->add(function (Request $request, Response $response) {

    $this->logger->info('Request Start');

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
        // 通常 Action 是非常薄的一层，仅用于权限、参数校验，完成所有的前置条件后，通过调用 service 层实现业务逻辑
        $action = new $ActionClass($this);
        $result = $action->execute();
        if ($result instanceof Response) {
            $response = $result;
        }
        else {
            $action->render($response);
        }
    }
    else {
        throw new Exception('Action not found', \App\Constant\Code::RESOURCE_NOT_FOUND);
    }

    // 正常结束的请求会打印 request end
    $this->logger->info('Request End');

    // 方便 Nginx 日志和 php 日志串起来
    $response = $response->withHeader('Request-Id', ID_REQUEST);

    return $response;

});

$app->run();

/**
 * 这个架构还是有问题的
 *
 * 1. request 和 response 可以不断经过中间件修改，但是容器拿到的还是最初的对象
 */
