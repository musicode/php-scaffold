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
    return pathinfo($file, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR
        . pathinfo($file, PATHINFO_FILENAME) . '.' . ENV
        . ($extname ? '.' . $extname : '');
}

function require_file_by_env($file) {
    return require(get_file_by_env($file));
}

/**
 * @return {int} 当前时间的毫秒数
 */
function getTimestamp() {
    return intval(microtime(true) * 1000);
}