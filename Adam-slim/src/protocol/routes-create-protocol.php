<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/create-protocol', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    $tplVars['id'] = $id;
    try {
        $stmt = $this->db->prepare('SELECT * FROM rezervacia
                                    WHERE rezervacia_key=:id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }

    $tplVars['reservation'] = $stmt->fetchAll();
    try {
        $stmt = $this->db->prepare('SELECT * FROM rezervacia
                                    WHERE rezervacia_key=:id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }

    $tplVars['zamestnanec'] = $stmt->fetchAll();
    try {
        $stmt = $this->db->prepare('SELECT zamestnanec_key, meno, priezvisko FROM zamestnanec_key');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }




    return $this->view->render($response, 'create-protocol.latte',$tplVars);
})->setName('create-protocol');