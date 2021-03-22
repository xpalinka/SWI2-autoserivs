<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/login', function(Request $request, Response $response, $args) {
    return $this->view->render($response, 'login.latte');
})->setName('login');
//<a href="{link login}"> neno <form action="{link login}">

$app->post('/login', function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    if(!empty($data['login']) && !empty($data['pass'])) {
        try {
            $stmt = $this->db->prepare('SELECT * FROM account WHERE login = :l');
            $stmt->bindValue(':l', $data['login']);
            $stmt->execute();
            $user = $stmt->fetch();
            if(!empty($user)) {
                if(password_verify($data['pass'], $user['password'])) {
                    $_SESSION['user'] = $user;
                    return $response->withHeader('Location',
                        $this->router->pathFor('persons'));
                }
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            die($e->getMessage());
        }
    }
    $tplVars['error'] = 'Chyba prihlaseni';
    return $this->view->render($response, 'login.latte', $tplVars);
});


$app->get('/new-user', function(Request $request, Response $response, $args) {
    $tplVars['form'] = ['l' => '', 'p' => ''];
    return $this->view->render($response, 'new-user.latte', $tplVars);
})->setName('newUser');

$app->post('/new-user', function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();  //$_POST
    if(!empty($data['l']) && !empty($data['p'])) {
        try {
            $stmt = $this->db->prepare('INSERT INTO account 
                                            (login, password)
                                            VALUES
                                            (:l, :p)');
            $stmt->bindValue(':l', $data['l']);
            $stmt->bindValue(':p', password_hash($data['p'],PASSWORD_DEFAULT));
            $stmt->execute();
        } catch (Exception $ex) {
            if($ex->getCode() == 23505) {
                $tplVars['error'] = 'Tento uzivatel uz existuje.';
                $tplVars['form'] = $data;
                return $this->view->render($response, 'new-user.latte', $tplVars);
            } else {
                $this->logger->error($ex->getMessage());
                die($ex->getMessage());
            }
        }
        return $response->withHeader('Location', $this->router->pathFor('login'));
    } else {
        $tplVars['error'] = 'Nejsou vyplneny vsechny udaje.';
        $tplVars['form'] = $data;
        return $this->view->render($response, 'new-user.latte', $tplVars);
    }
});


