<?php

namespace App\Component;

use Monolog\Handler\StreamHandler;

class AggregateStreamHandler extends StreamHandler {

    private $request;

    public function __construct($stream, $level, $request) {
        parent:: __construct($stream, $level);
        $this->request = $request;
    }

    public function handleBatch(Array $records) {
        // 请求结束时会走进这里批量写日志

        $request = $this->request;

        $log = sprintf(
            "\n[%s][%sms][%s]%s %s %s\n",
            date('Y-m-d H:i:s', TIME_REQUEST_START / 1000),
            number_format(get_timestamp() - TIME_REQUEST_START),
            ID_REQUEST,
            get_client_ip($request->getServerParams(), $request, true),
            $request->getMethod(),
            $request->getUri()->getPath()
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
