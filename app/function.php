<?php

/**
 * 扩展一些常用函数
 *
 * 按 php 语言规范，统一用下划线定义全局函数
 */

/**
 * 按当前环境读取文件，比如有 a.dev.php、a.test.php、a.prod.php
 * 不同环境读取不同的文件
 *
 * @param {string} file 类似 path/a.php 的文件路径
 * @return {string} 加上当前环境的文件路径
 */
function get_file_by_env($file) {
    $extname = pathinfo($file, PATHINFO_EXTENSION);
    return pathinfo($file, PATHINFO_DIRNAME) . DS
        . pathinfo($file, PATHINFO_FILENAME) . '.' . ENV
        . ($extname ? '.' . $extname : '');
}

/**
 * 区分环境的文件大多是配置文件，因此提供一个函数直接读取配置
 *
 * @param {string} file
 * @return {Array}
 */
function require_file_by_env($file) {
    return require(get_file_by_env($file));
}

/**
 * 获取当前时间的毫秒数
 *
 * 毫秒有两个好处：
 *
 * 1. 与前端统一时间单位
 * 2. 能应付大多数场景，比如打点计时
 *
 * @return {int}
 */
function get_timestamp() {
    return intval(microtime(true) * 1000);
}

/**
 * 校验 IP
 *
 * @return {boolean}
 */
function validate_ip($ip) {
    $flags = FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6;
    return filter_var($ip, FILTER_VALIDATE_IP, $flags) !== false;
}

/**
 * 获取客户端的 IP
 *
 * @param {Array} $servers
 * @return {int}
 */
function get_client_ip($servers, $request = null, $checkProxyHeaders = false, $trustedProxies = null) {

    if (isset($servers['REMOTE_ADDR']) && validate_ip($servers['REMOTE_ADDR'])) {
        $ip = $servers['REMOTE_ADDR'];
    }

    if ($checkProxyHeaders && !empty($trustedProxies)) {
        if (!in_array($ip, $trustedProxies)) {
            $checkProxyHeaders = false;
        }
    }

    if ($checkProxyHeaders) {
        $headersToInspect = [
            'Forwarded',
            'X-Forwarded-For',
            'X-Forwarded',
            'X-Cluster-Client-Ip',
            'Client-Ip',
        ];
        foreach ($headersToInspect as $header) {
            if ($request->hasHeader($header)) {
                $items = explode(',', $request->getHeaderLine($header));
                $ip = trim($items[0]);
                if (ucfirst($header) == 'Forwarded') {
                    foreach (explode(';', $ip) as $headerPart) {
                        if (strtolower(substr($headerPart, 0, 4)) == 'for=') {
                            $for = explode(']', $headerPart);
                            $ip = trim(substr($for[0], 4), " \t\n\r\0\x0B" . "\"[]");
                            break;
                        }
                    }
                }
                if (validate_ip($ip)) {
                    break;
                }
            }
        }
    }

    return $ip;

}

/**
 * 获取返回的 json 数据
 *
 * @param $code 请用 App\Constant\Code 里面的常量
 * @param $data
 * @param $message
 */
function format_response($code, $data = [], $message = '') {
    return [
        'code' => $code,
        'data' => $data,
        'msg' => $message,
        'ts' => get_timestamp(),
    ];
}

/**
 * 可翻页的列表数据返回结构
 *
 * @param $code 请用 App\Constant\Code 里面的常量
 * @param $list 当前页的数据
 * @param $page 当前页码
 * @param $page_size 每页多少条数据
 * @param $total_size 总数据量
 * @param $message
 */
function format_list_response($code, $list, $page, $page_size, $total_size, $message = '') {
    return format_response(
        $code,
        [
            'list' => $list,
            'pager' => [
                'page' => $page,
                'count' => ceil($total_size / $page_size),
                'page_size' => $page_size,
                'total_size' => $total_size,
            ]
        ],
        $message
    );
}