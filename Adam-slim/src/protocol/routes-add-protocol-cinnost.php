<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/add-protocol-material', function(Request $request, Response $response, $args) {

    try {
        $stmt = $this->db->prepare("SELECT * FROM material");
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['materials'] = $stmt->fetchAll();

        return $this->view->render($response, 'add-protocol-cinnost.latte',$tplVars);

});