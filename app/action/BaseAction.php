<?php

namespace App\Action;

use Slim\Container;

class BaseAction {

    /**
     * CI 容器
     *
     * @type {Slim\Container}
     */
    protected $container;

    /**
     * 请求带来的参数
     *
     * @type {Array}
     */
    protected $params;

    /**
     * 参数校验
     *
     * @type {Array}
     */
    protected $validator;

    /**
     * 处理后的返回对象
     *
     * @type {Slim\Http\Response}
     */
    protected $response;

    public function __construct(Container $container) {
        $this->container = $container;
        $this->response = $container->response;

        // 合并 GET 和 POST 参数
        $params = $container->request->getParams();

        // access_token 用于身份校验，是比较特殊的参数
        // 对于非浏览器，通过参数传递
        // 对于浏览器，一般会存在 cookie 中，而不需要传参
        if (!isset($params['access_token'])) {
            $params['access_token'] = $container->request->getCookieParam('access_token');
        }
        $this->params = $params;

        $container->logger->info('Request Params', $params);

    }

    protected function validate($validators, $values) {
        
    }

    public function execute() {
        return $this->response;
    }

}