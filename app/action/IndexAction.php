<?php

namespace App\Action;

use App\Constant\Code;
use Respect\Validation\Validator;

class IndexAction extends BaseAction {

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
            return $this->renderJson(Code::PARAM_INVALID, $result);
        }

        $query = $this->container->db->from('common_area');

        echo $query;


        return format_response(Code::PARAM_INVALID, $result);

    }
}