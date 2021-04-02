<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/create-user', function(Request $request, Response $response, $args) {
    $tplVars['form'] = ['ln' => '', 'fn' => '', 'rc' => '', 'r' => '', 'tf' => '', 't' => '', 'bu' => '', 'm' => '', 'ul' => '', 'oc' => '', 'pc' => '', 'zip' => '', 'st' => ''];
    try {
        $stmt = $this->db->prepare('SELECT * FROM pozicia');
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['pozicie'] = $stmt->fetchAll();

    //keby chces vyberat z adres
    try {
        $stmt = $this->db->prepare('SELECT * FROM adresa');
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['adresy'] = $stmt->fetchAll();

    return $this->view->render($response, 'create-user.latte', $tplVars);
})->setName('create-user');

$app->post('/create-user', function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();  //$_POST
    if(!empty($data['ln']) && !empty($data['fn']) && !empty($data['rc']) && !empty($data['r']) && !empty($data['bu']) && !empty($data['tf']) && !empty($data['t']) && (!empty($data['adresa_key']) || !empty($data['t'])) ) {
        try {
            $this->db->beginTransaction();

            if(empty($data['adresa_key'])) {
                $stmt = $this->db->prepare('INSERT INTO adresa
                                      (mesto, ulica, orientacne_cislo, popisne_cislo, psc, stat)
                                       VALUES 
                                      (:m, :ul, :oc, :oc, :pc, :zip, :st)');
                $stmt->bindValue(':m', $data['m']);
                $stmt->bindValue(':ul', $data['ul']);
                $stmt->bindValue(':oc', $data['oc'] == '' ? null : $data['oc']);
                $stmt->bindValue(':pc', $data['pc'] == '' ? null : $data['pc']);
                $stmt->bindValue(':zip', $data['zip']);
                $stmt->bindValue(':st', $data['st']);
                $stmt->execute();
                $idAddress = $this->db->lastInsertId('adresa_adresa_key_seq');
            } else {
                //keby chces vyberat z adres
                $idAddress = $data['adresa_key'];
            }

            $stmt=$this->db->prepare('INSERT INTO zamestnanec
                                      (meno, prizvisko, rodne_cislo, email, heslo, bankovy_ucet, telefon, adresa_key, pozicia_key)
                                       VALUES 
                                      (:fn, :ln, :rc, :r, :heslo, :bu, :tf, :adr, :poz)');
            $stmt->bindValue(':fn', $data['fn']);
            $stmt->bindValue(':ln', $data['ln']);
            $stmt->bindValue(':rc', $data['rc']);
            $stmt->bindValue(':r', $data['r']);
            $heslo = password_hash("HESLO", PASSWORD_DEFAULT);
            $stmt->bindValue(':heslo', $heslo);
            $stmt->bindValue(':bu', $data['bu']);
            $stmt->bindValue(':tf', $data['tf']);
            $stmt->bindValue(':adr', $idAddress);
            $stmt->bindValue(':poz', $data['t']);
            $stmt->execute();

            $this->db->commit();
//            $tplVars['done'] = 'Operácia úspešná.';
        } catch (Exception $ex) {
            $this->db->rollback();
            if($ex->getCode() == 23505) {
                $tplVars['error'] = 'Tento užívateľ už existuje.';
                $tplVars['form'] = $data;
                return $this->view->render($response, 'create-user.latte', $tplVars);
            } else {
                $this->logger->error($ex->getMessage());
                die($ex->getMessage());
            }
        }
        return $response->withHeader('Location', $this->router->pathFor('home'));
    } else {
        $tplVars['error'] = 'Nie sú vyplnené všetky údaje.';
        $tplVars['form'] = $data;
        return $this->view->render($response, 'create-user.latte', $tplVars);
    }
});