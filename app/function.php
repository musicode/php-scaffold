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
 * 获取返回的 json 数据
 *
 * @param $code 请用 App\Constant\Code 里面的常量
 * @param $data
 * @param $message
 */
function format_response($code, $data, $message = '') {
    return [
        'code' => $code,
        'data' => $data,
        'msg' => $message,
        'ts' => get_timestamp(),
    ];
}