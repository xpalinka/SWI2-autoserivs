<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/reservations', function (Request $request, Response $response, $args) {

    return $this->view->render($response, 'reservations.latte');
})->setName('reservations');