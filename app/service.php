<?php

// 不写类型没有代码提示...
use Slim\Http\Request;
use Slim\Http\Response;

// 日志库
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\BufferHandler;

use App\Component\Security;
use App\Component\SmartyView;
use App\Component\AggregateStreamHandler;

use App\Exception\DataException;

return [
    // 所有异常处理
    'errorHandler' => function ($container) {
        return function (Request $request, Response $response, Exception $exception) use ($container) {
            $code = $exception->getCode();
            $message = $exception->getMessage();
            $data = [];
            if ($exception instanceof DataException) {
                $data = $exception->getData();
            }
            $container->logger->error('Exception: ' . $code . ' ' . $message, $data);
            $response->write(
                json_encode(format_response($code, $data, $message))
            );
            return $response->withHeader('Request-Id', ID_REQUEST);
        };
    },
    // 日志
    'logger' => function ($container) {

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

    },
    // Mysql
    'db' => function ($container) {

        $settings = $container->get('settings')['db'];

        $pdo = new PDO(
            "mysql:host=${settings['host']};port=${settings['port']};dbname=${settings['dbname']};charset=${settings['charset']}",
            $settings['username'],
            $settings['password']
        );

        return new FluentPDO($pdo);

    },
    // Redis
    'redis' => function ($container) {

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

    },
    // 模板引擎
    'view' => function ($container) {

        $settings = $container->get('settings')['view'];

        return new SmartyView(
            $settings['template_dir'],
            $settings['compile_dir'],
            $settings['cache_dir']
        );

    },
    // 密码加密
    'security' => function ($container) {
        return new Security();
    },
];
