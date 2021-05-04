<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/create-protocol', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    $tplVars['id'] = $id;
    try {
        $stmt = $this->db->prepare('SELECT rezervacia.id AS rezervacia_key
                                    FROM rezervacia

                                    WHERE rezervacia_key = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['reservation'] = $stmt->fetchAll();
    try {
        $stmt = $this->db->prepare('SELECT 
                                    reservation.datum AS rezervacia_datum_vytvorenia, reservation.key AS rezervacia_key
                                    FROM  rezervacia
                                    WHERE protokol_key = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }


    return $this->view->render($response, 'create-protocol.latte');
})->setName('create-protocol');