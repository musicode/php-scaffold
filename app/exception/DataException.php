<?php

namespace App\Exception;

class DataException extends \Exception {

    private $data;

    public function __construct($message, $code, $data) {
        parent:: __construct($message, $code);
        $this->data = $data;
    }

    public function getData() {
        return $this->data;
    }

}