<?php

namespace App\Action;

use App\Constant\Code;
use Respect\Validation\Validator;

class IndexAction extends BaseAction {

    public function execute() {

        $result = $this->validate(
            [
                'name' => [ Validator::alnum()->noWhitespace()->length(3, 5), '缺少 name' ]
            ],
            $this->params
        );

        if ($result === true) {
            return $this->renderJson(Code::SUCCESS, $result);
        }

        return $this->renderJson(Code::PARAM_INVALID, $result);

    }
}