<?php

namespace App\Component;

class Security {

    public function hash($password) {
        return password_hash(
            $password,
            PASSWORD_BCRYPT,
            [
                'salt' => 19,
                'cost' => 10
            ]
        );
    }

    public function verify($password, $hash) {
        return password_verify($password, $hash);
    }

}