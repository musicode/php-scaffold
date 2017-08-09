<?php

namespace App\Action;

use App\Constant\Code;
use App\Constant\RenderType;
use Respect\Validation\Validator;

class IndexAction extends BaseAction {

    protected $renderType = RenderType::HTML;
    protected $renderTemplate = 'index.html';

    public function execute() {

        $result = $this->validate(
            [
                'name' => [
                    Validator::alnum()->notEmpty(), '缺少 name',
                    Validator::alnum()->noWhitespace()->length(2, 10), 'name 不合法'
                ]
            ],
            $this->params
        );

        if ($result !== true) {
            return format_response(Code::PARAM_INVALID, $result);
        }

        // 测试 db
        $query = $this->container->db->from('common_area')->where('id < ?', 1010);

        return format_response(Code::SUCCESS, $query->fetchAll());

        // 测试 redis
//        $this->container->redis->set('test', '213');
//
//        return format_response(Code::SUCCESS, $this->container->redis->get('test'));


    }
}