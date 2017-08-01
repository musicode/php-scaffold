<?php


namespace App\Component;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\BufferHandler;
use Monolog\Handler\StreamHandler;

class AggregateFileHandler extends StreamHandler {

    public function handleBatch(Array $records) {
        // 请求结束时会走进这里批量写日志
        // https://laravel-china.org/articles/3567/monolog-optimization-and-elk-friendly-log-format
        // 请求耗时
        $duration = number_format(microtime(true) - TIME_REQUEST_START, 3);

        $format  = 'Y-m-d H:i:s.u';

        $log = sprintf(
            '[%s][%s]%s %s %s\n',
            $duration,
            $container->get('request')
        );

        foreach ($records as $record) {
            if ($this->isHandling($record)) {
                $record = $this->processRecord($record);
                $log .= $this->getFormatter()->format($record);
            }
        }

        $this->write(['formatted' => $log]);

    }

}



return function ($container) {

    $request = $container->get('request');

    $settings = $container->get('settings')['logger'];

    // 以日期为目录
    // 每一天的目录下按小时拆分文件
    $path = $settings['dir'] . DIRECTORY_SEPARATOR
        . ENV . DIRECTORY_SEPARATOR
        . date('Y-m-d') . DIRECTORY_SEPARATOR
        . date('H') . '.log';

    $logger = new Logger($settings['name']);

    $handler = new AggregateFileHandler($path);
    $handler->setFormatter(
        new LineFormatter('[%datetime%]%level_name% %message% %context% %extra%\n', 'i:s', true, true)
    );

    $logger->pushHandler(new BufferHandler($handler));

    return $logger;

};