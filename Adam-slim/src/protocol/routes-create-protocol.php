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


    try {
        $stmt = $this->db->prepare('SELECT * FROM rezervacia
                                    WHERE rezervacia_key=:id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex-é>getMessage());
    }
    $tplVars['reservation'] = $stmt->fetchAll();

    try {
        $stmt = $this->db->prepare('SELECT * FROM zamestnanec');
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['zamestnanec'] = $stmt->fetchAll();




    return $this->view->render($response, 'create-protocol.latte',$tplVars);
})->setName('create-protocol');

$app->post('/create-protocol', function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();  //$_POST
    if(!empty($data['dv']) && !empty($data['pz']) && !empty($data['z']) && !empty($data['r']) ) {
        try {
            $this->db->beginTransaction();

            $stmt=$this->db->prepare('INSERT INTO protokol
                                      (datum_vystavenia, posledna_zmena, zamestnanec_key, rezervacia_key) 
                                    VALUES
                                      (:dv, :pz, :z, :r)');
            $stmt->bindValue(':dv', $data['dv']);
            $stmt->bindValue(':pz', $data['pz']);
            $stmt->bindValue(':z', $data['z']);
            $stmt->bindValue(':r', $data['r']);

            $stmt->execute();
//
            $this->db->commit();

        } catch (Exception $ex) {
            $this->db->rollback();
            if($ex->getCode() == 23505) {
                print $ex->getMessage();
                $tplVars['error'] = 'Tento prtokol už existsuje.';
                try {
                    $stmt = $this->db->prepare('SELECT * FROM pozicia');
                    $stmt->execute();
                } catch (Exception $ex) {
                    $this->logger->error($ex->getMessage());
                    die($ex->getMessage());
                }
                $tplVars['pozicie'] = $stmt->fetchAll();
                $tplVars['form'] = $data;
                return $this->view->render($response, 'create-protocol.latte', $tplVars);
            } else {
                $this->logger->error($ex->getMessage());
                die($ex->getMessage());
            }
        }
        return $response->withHeader('Location', $this->router->pathFor('protocols'));
    } else {
        $tplVars['error'] = 'Nie sú vyplnené všetky údaje.';
        $tplVars['form'] = $data;
        return $this->view->render($response, 'create-protocol.latte', $tplVars);
    }

});