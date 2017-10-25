<?php

namespace App\Action;

use App\Constant\Code;
use App\Constant\RenderType;
use Ramsey\Uuid\Uuid;
use Slim\Container;

class BaseAction {

    /**
     * CI 容器
     *
     * @var Slim\Container
     */
    protected $container;

    /**
     * 请求带来的参数
     *
     * @var Array
     */
    protected $params;

    /**
     * 模板路径
     *
     * @var string
     */
    protected $renderTemplate;

    /**
     * 渲染方式，默认 json
     *
     * @var string
     */
    protected $renderType = RenderType::JSON;

    /**
     * 处理结果的数据
     *
     * @var array
     */
    protected $data = [];

    public function __construct(Container $container) {

        $this->container = $container;

        // 合并 GET 和 POST 参数
        $params = $container->request->getParams();

        // access_token 用于身份校验，是比较特殊的参数
        // 对于非浏览器，通过参数传递
        // 对于浏览器，一般会存在 cookie 中，而不需要传参
        if (!isset($params['access_token'])) {
            $accessToken = $container->request->getCookieParam('access_token');
            if (is_null($accessToken)) {
                $this->data['access_token'] = Uuid::uuid1()->getHex();
            }
            else {
                $params['access_token'] = $accessToken;
            }
        }
        if (isset($params['renderType'])) {
            $this->renderType = $params['renderType'];
        }
        else if (isset($params['callback'])) {
            $this->renderType = RenderType::JSONP;
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
     * @return 校验成功时，返回 true，校验失败时，返回错误信息的数组
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

    public function redirect($uri) {
        return $this->container->response->withStatus(302)->withHeader('Location', $uri);
    }

    public function render($response) {
        $this->container->logger->info('Execute Result', $this->data);
        switch ($this->renderType) {
            case RenderType::HTML:
                $content = $this->renderHtml();
                break;
            case RenderType::JSON:
                $content = $this->renderJson();
                break;
            case RenderType::JSONP:
                $content = $this->renderJsonp();
                break;
        }
        $response->write($content);
    }

    protected function renderHtml() {
        return $this->container->view->render(
            $this->renderTemplate,
            [
                'tpl_data' => $this->data
            ]
        );
    }

    protected function renderJson() {
        return json_encode(
            format_response(Code::SUCCESS, $this->data)
        );
    }

    protected function renderJsonp() {
        return $this->params['callback'] . '(' . $this->renderJson() . ');';
    }

}