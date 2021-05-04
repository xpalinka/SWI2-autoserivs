<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/add-protocol-material', function(Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
//    $tplVars['id'] = $id;
//    $tplVars['material'] = '';
//    $tplVars['mnozstvo_materialu'] = 0;
    $tplVars['form'] = ['id' => $id, 'material' => '', 'mnozstvo_materialu' => 0];

    try {
        $stmt = $this->db->prepare("SELECT * FROM material");
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['materials'] = $stmt->fetchAll();

    try {
        $stmt = $this->db->prepare("SELECT * FROM polozka_protokolu WHERE polozka_protokolu_key = :id
                                    LEFT JOIN typ_opravy USING(typ_opravy_key)");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['polozka'] = $stmt->fetch();

    return $this->view->render($response, 'add-protocol-material', $tplVars);
})->setName('add-protocol-material');

$app->post('/add-protocol-material', function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();  //$_POST
    if ($data['mnozstvo_materialu'] != 0 && !empty($data['material'])) {
        try {
            $this->db->beginTransaction();

//            if(empty($data['adresa_key'])) {
//                $stmt = $this->db->prepare('INSERT INTO adresa
//                                      (mesto, ulica, orientacne_cislo, popisne_cislo, psc, stat)
//                                       VALUES
//                                      (:m, :ul, :oc, :pc, :zip, :st)');
////                $stmt->bindValue(':id', );
//                $stmt->bindValue(':m', $data['m']);
//                $stmt->bindValue(':ul', $data['ul']);
//                $stmt->bindValue(':oc', $data['oc'] == '' ? null : $data['oc']);
//                $stmt->bindValue(':pc', $data['pc'] == '' ? null : $data['pc']);
//                $stmt->bindValue(':zip', $data['zip']);
//                $stmt->bindValue(':st', $data['st']);
//                $stmt->execute();
//                $idAddress = $this->db->lastInsertId('adresa_adresa_key_seq');
//            } else {
//                //keby chces vyberat z adres
//                $idAddress = $data['adresa_key'];
//            }
//
//            $stmt=$this->db->prepare('INSERT INTO zamestnanec
//                                      (meno, priezvisko, rodne_cislo, email, heslo, bankovy_ucet, telefon, adresa_key, pozicia_key)
//                                       VALUES
//                                      (:fn, :ln, :rc, :r, :heslo, :bu, :tf, :adr, :poz)');
//            $stmt->bindValue(':fn', $data['fn']);
//            $stmt->bindValue(':ln', $data['ln']);
//            $stmt->bindValue(':rc', $data['rc']);
//            $stmt->bindValue(':r', $data['r']);
//            $heslo = password_hash("HESLO", PASSWORD_DEFAULT);
//            $stmt->bindValue(':heslo', $heslo);
//            $stmt->bindValue(':bu', $data['bu']);
//            $stmt->bindValue(':tf', $data['tf']);
//            $stmt->bindValue(':adr', $idAddress);
//            $stmt->bindValue(':poz', $data['t']);
//            $stmt->execute();
//
            $this->db->commit();
//            $tplVars['done'] = 'Operácia úspešná.';
        } catch (Exception $ex) {
            $this->db->rollback();
            if($ex->getCode() == 23505) {
                print $ex->getMessage();
                $tplVars['error'] = 'Táto spotreba už existuje.';
                $tplVars['form'] = $data;
                return $this->view->render($response, 'add-protocol-material.latte', $tplVars);
            } else {
                $this->logger->error($ex->getMessage());
                die($ex->getMessage());
            }
        }



        return $response->withHeader('Location', $this->router->pathFor('protocols'));
    } else {
        $tplVars['error'] = 'Nie sú vyplnené všetky údaje.';
        $tplVars['form'] = $data;
        return $this->view->render($response, 'add-protocol-material.latte', $tplVars);
    }
});