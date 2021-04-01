<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/details-protocol', function (Request $request, Response $response, $args) {
})->setName('details-protocol');