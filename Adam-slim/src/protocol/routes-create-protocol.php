<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/create-protocol', function (Request $request, Response $response, $args) {
    return $this->view->render($response, 'create-protocol.latte');
})->setName('create-protocol');