<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/delete-protocol', function (Request $request, Response $response, $args){
})->setName('delete-protocol');