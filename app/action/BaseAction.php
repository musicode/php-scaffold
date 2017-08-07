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

    public function __construct(Container $container) {

        $this->container = $container;

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

    /**
     * 参数校验，只验证扁平结构，即不能数组里面包含数组，如有复杂数组需要校验，请多次调用 validate()
     *
     * @param $validators
     * @param $values
     * @param $stopOnError
     * @return 当校验成功时，返回 true，校验失败时，返回错误信息的数组
     */
    protected function validate($validators, $values, $stopOnError = true) {

        // 校验库是 https://github.com/Respect/Validation，超级强大，连上传文件都能校验...
        $errors = false;

        foreach ($validators as $key => $validator) {
            for ($i = 0, $len = count($validator); $i < $len; $i += 2) {
                if (!$validator[$i](isset($values[$key]) ? $values[$key] : null)) {
                    if ($errors === false) {
                        $errors = [ ];
                    }
                    $errors[$key] = $validator[$i + 1];
                    if ($stopOnError) {
                        break;
                    }
                }
            }
        }

        return $errors === false ? true : $errors;

    }

}