<?php

namespace App\Constant;

class Code {

    const SUCCESS = 0;

    // 客户端通用错误
    const CLIENT_ERROR = 400;

    // 参数非法
    const PARAM_INVALID = 401;

    // 资源不存在
    const RESOURCE_NOT_FOUND = 404;

    // 资源已存在
    const RESOURCE_EXISTS = 405;

}