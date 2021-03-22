<?php
/**
 * Created by PhpStorm.
 * User: Asus
 * Date: 12. 3. 2019
 * Time: 9:24
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/add-user', function (Request $request, Response $response, $args) {


    return $this->view->render($response, 'add-user.latte');
})->setName('addUser');
