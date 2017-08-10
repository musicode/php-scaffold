<?php

require 'vendor/autoload.php';
require 'app/function.php';

// 不写类型没有代码提示...
use Slim\Http\Request;
use Slim\Http\Response;

// 日志库
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BufferHandler;

use Ramsey\Uuid\Uuid;
use Underscore\Types\Strings;

use App\Component\Security;
use App\Component\SmartyView;
use App\Component\AggregateStreamHandler;

use App\Exception\DataException;

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
    'settings' => require_file_by_env('app/config/config.php')
]);

$container = $app->getContainer();
// 所有异常处理
$container['errorHandler'] = function ($container) {
    return function (Request $request, Response $response, Exception $exception) use ($container) {
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $data = null;
        if ($exception instanceof DataException) {
            $data = $exception->getData();
        }
        $container->logger->error('Exception: [' . $code . '] ' . $message, $data);
        $response->write(
            json_encode(format_response($code, $data, $message))
        );
        return $response->withHeader('Request-Id', ID_REQUEST);
    };
};
// 日志
$container['logger'] = function ($container) {

    $request = $container->get('request');

    $settings = $container->get('settings')['logger'];

    // 以日期为目录
    // 每一天的目录下按小时拆分文件
    $path = $settings['dir'] . DS
        . ENV . DS
        . date('Y-m-d') . DS
        . date('H') . '.log';

    $logger = new Logger($settings['name']);

    $handler = new AggregateStreamHandler($path, $settings['level'], $request);

    $handler->setFormatter(
        new LineFormatter("           [%datetime%][%level_name%] %message% %context% %extra%\n", 'H:i:s', true, true)
    );

    $logger->pushHandler(new BufferHandler($handler));

    return $logger;

};
// Mysql
$container['db'] = function ($container) {

    $settings = $container->get('settings')['db'];

    $pdo = new PDO(
        "mysql:host=${settings['host']};port=${settings['port']};dbname=${settings['dbname']};charset=${settings['charset']}",
        $settings['username'],
        $settings['password']
    );

    return new FluentPDO($pdo);

};
// Redis
$container['redis'] = function ($container) {

    $settings = $container->get('settings')['redis'];

    $options = [
        'scheme' => 'tcp',
        'host'   => $settings['host'],
        'port'   => $settings['port'],
    ];

    // redis 比较奇怪，传了空密码会报错
    if ($settings['password'] !== '') {
        $options['password'] = $settings['password'];
    }

    return new Predis\Client($options);

};
// 模板引擎
$container['view'] = function ($container) {

    $settings = $container->get('settings')['view'];

    return new SmartyView(
        $settings['template_dir'],
        $settings['compile_dir'],
        $settings['cache_dir']
    );

};
// 密码加密
$container['security'] = function ($container) {
    return new Security();
};

$app->add(new RKA\Middleware\IpAddress(true, ['10.0.0.1', '10.0.0.2']));

$app->add(function (Request $request, Response $response, Callable $next) {

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
        $action->render(
            $action->execute()
        );
    }
    else {
        throw new Exception('Action not found', \App\Constant\Code::RESOURCE_NOT_FOUND);
    }

    // 正常结束的请求会打印 request end
    $this->logger->info('Request End');

    // 方便 Nginx 日志和 php 日志串起来
    return $response->withHeader('Request-Id', ID_REQUEST);

});

$app->run();

