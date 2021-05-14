<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/add-protocol-material', function(Request $request, Response $response, $args) {

        return $this->view->render($response, 'add-protocol-cinnost.latte');

});