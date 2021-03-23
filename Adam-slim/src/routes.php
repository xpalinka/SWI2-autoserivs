<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function (Request $request, Response $response) {
    return $response->withHeader('Location', $this->router->pathFor('home'));
});

$app->group('/auth', function () use ($app) {
    include('routes-reservations.php');
    $app->get('/logout', function (Request $request, Response $response) {
        session_destroy();
        return $response->withHeader('Location', $this->router->pathFor('login'));
    })->setName('logout');
})->add(function (Request $request, Response $response, $next) {
    if (!empty($_SESSION['user'])) {
        return $next($request, $response);
    } else {
        return $response->withHeader('Location', $this->router->pathFor('login'));
    }
});

$app->get('/home', function (Request $request, Response $response, $args) {

    return $this->view->render($response, 'home.latte');
})->setName('home');



//$app->get('/', function (Request $request, Response $response) {
//    return $response->withHeader('Location', $this->router->pathFor('persons'));
//});

//$app->group('/auth', function () use ($app) {
//    include('routes-person.php');
//    include('routes-contacts.php');
//    include('routes-location.php');
//    include ('routes-relationship.php');
//    include('details.php');
//
//    $app->get('/logout', function (Request $request, Response $response) {
//        session_destroy();
//        return $response->withHeader('Location', $this->router->pathFor('login'));
//    })->setName('logout');
//
//})->add(function (Request $request, Response $response, $next) {
//    if (!empty($_SESSION['user'])) {
//        return $next($request, $response);
//    } else {
//        return $response->withHeader('Location', $this->router->pathFor('login'));
//    }
//});

//include('routes-person.php');
//include('routes-contacts.php');
//include('routes-location.php');
//include('routes-relationship.php');
//include('details.php');

include('routes-login.php');
//include ('routes-job_site.php');