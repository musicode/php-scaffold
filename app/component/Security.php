<?php

namespace App\Component;

class Security {

    public function hash($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }

    public function verify($password, $hash) {
        return password_verify($password, $hash);
    }

}