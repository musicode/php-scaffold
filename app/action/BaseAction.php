<?php

namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;

class BaseAction {

    protected $request;
    protected $response;

    protected $params;

    public function __construct(Request $request, Response $response) {
        $this->request = $request;
        $this->response = $response;

        $params = $request->getParams();
        if (!isset($params['access_token'])) {
            $params['access_token'] = $request->getCookieParam('access_token');
        }
        $this->params = $params;

    }

    public function execute() {

    }

}