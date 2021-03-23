<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/reservations', function (Request $request, Response $response, $args) {

    try {
        $stmt = $this->db->prepare('SELECT * FROM rezervacia
                                    LEFT JOIN zakaznik USING (zakaznik_key)');
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }

    $tplVars['reservations'] = $stmt->fetchAll();

    return $this->view->render($response, 'reservations.latte', $tplVars);
})->setName('reservations');