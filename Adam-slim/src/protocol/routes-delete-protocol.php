<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/delete-protocol', function (Request $request, Response $response, $args){
    $id = $request->getQueryParam('id');
    try{
        $stmt = $this->db->prepare('DELETE FROM protokol WHERE protokol_key=:id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex){
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    return $response->withHeader('Location', $this->router->pathFor('protocols'));
})->setName('delete-protocol');