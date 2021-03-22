<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/details', function(Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    try {
        $stmt = $this->db->prepare('SELECT * FROM location
                                    WHERE id_location IN 
                                      (SELECT id_location FROM person 
                                          WHERE id_person = :id)'
        );
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['locations'] = $stmt->fetchAll();

    try {
        $stmt = $this->db->prepare('SELECT * FROM contact 
                                    LEFT JOIN contact_type ON 
                                    contact.id_contact_type = contact_type.id_contact_type
                                    WHERE id_person = :id
                                    ORDER BY name');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['contacts'] = $stmt->fetchAll();
    $tplVars['id'] = $id;

    return $this->view->render($response, 'details.latte', $tplVars);
})->setName('details');