<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/login', function(Request $request, Response $response, $args) {
    return $this->view->render($response, 'login.latte');
})->setName('login');

$app->post('/login', function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();
    if(!empty($data['login']) && !empty($data['pass'])) {
        try {
            $stmt = $this->db->prepare('SELECT * FROM zamestnanec WHERE email = :l');
            $stmt->bindValue(':l', $data['login']);
            $stmt->execute();
            $user = $stmt->fetch();
            print $data['pass'];
            print password_hash($data['pass'], PASSWORD_DEFAULT);
            print $user['heslo'];
            if(!empty($user)) {
                if(password_verify($data['pass'], $user['heslo'])) {
                    $_SESSION['user'] = $user;
                    return $response->withHeader('Location',
                        $this->router->pathFor('home'));
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