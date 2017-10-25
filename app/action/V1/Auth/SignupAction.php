<?php

namespace App\Action\V1\Auth;

use App\Constant\Code;
use App\Exception\DataException;
use App\Service\Account\UserService;
use Respect\Validation\Validator;

class SignupAction extends BaseAction {

    public function execute() {

        $result = $this->validate(
            [
                'username' => [
                    Validator::alnum()->notEmpty(), '缺少 username',
                    Validator::alnum()->noWhitespace()->length(2, 10), 'username 不合法'
                ],
                'password' => [
                    Validator::alnum()->notEmpty(), '缺少 password',
                    Validator::alnum()->noWhitespace()->length(2, 10), 'password 不合法'
                ]
            ],
            $this->params
        );

        if ($result !== true) {
            throw new DataException('Param invalid', Code::PARAM_INVALID, $result);
        }

        UserService::signup([
            'username' => $this->params['username'],
            'password' => $this->params['password']
        ]);

    }
}
