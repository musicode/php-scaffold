<?php

namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;

class BaseAction {

    protected $request;
    protected $response;

    /**
     * 请求带来的参数
     *
     * @type {Array}
     */
    protected $params;

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;

        // 合并 GET 和 POST 参数
        $params = $request->getParams();
        // access_token 用于身份校验，是比较特殊的参数
        // 对于非浏览器，通过参数传递
        // 对于浏览器，一般会存在 cookie 中，而不需要传参
        if (!isset($params['access_token'])) {
            $params['access_token'] = $request->getCookieParam('access_token');
        }
        $this->params = $params;

    }

    public function execute() {

    }

}