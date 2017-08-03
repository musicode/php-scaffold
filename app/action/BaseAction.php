<?php

namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;

class BaseAction {


    public function execute(Request $request, Response $response) {

        $params = $request->getParams();

        if (!isset($params['access_token'])) {
            $params['access_token'] = $request->getCookieParam('access_token');
        }



    }

}