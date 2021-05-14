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
        $stmt = $this->db->prepare("SELECT * FROM polozka_protokolu
                                    LEFT JOIN typ_opravy USING(typ_opravy_key)
                                    WHERE polozka_protokolu_key = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['polozka'] = $stmt->fetch();

    return $this->view->render($response, 'add-protocol-material.latte', $tplVars);
})->setName('add-protocol-material');

$app->post('/add-protocol-material', function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();  //$_POST
    if (true) {
        try {
            $this->db->beginTransaction();
            try {
                $stmt = $this->db->prepare("SELECT * FROM material
                                            LEFT JOIN skladova_karta USING(material_key)
                                            WHERE material_key = :id");
                $stmt->bindValue(':id', $data['material_key']);
                $stmt->execute();
            } catch (Exception $ex) {
                $this->logger->error($ex->getMessage());
                die($ex->getMessage());
            }
            $material = $stmt->fetch();
            $skladova_karta_key = $material['skladova_karta_key'];

            $stmt=$this->db->prepare('INSERT INTO spotreba_materialu
                                      (mnozstvo, skladova_karta_key, polozka_protokolu_key)
                                       VALUES
                                      (:mnozstvo, :skladova_karta_key, :polozka_protokolu_key)');
            $stmt->bindValue(':mnozstvo', $data['mnozstvo_materialu']);
            $stmt->bindValue(':skladova_karta_key', $skladova_karta_key);
            $stmt->bindValue(':polozka_protokolu_key', $data['polozka_protokolu_key']);
            $stmt->execute();
            $this->db->commit();
//            $tplVars['done'] = 'Operácia úspešná.';
        } catch (Exception $ex) {
            $this->db->rollback();
            if($ex->getCode() == 23505) {
                print $ex->getMessage();
                $tplVars['error'] = 'Táto spotreba už existuje.';
                $tplVars['form'] = $data;
                try {
                    $stmt = $this->db->prepare("SELECT * FROM material");
                    $stmt->execute();
                } catch (Exception $ex) {
                    $this->logger->error($ex->getMessage());
                    die($ex->getMessage());
                }
                $tplVars['materials'] = $stmt->fetchAll();
                try {
                    $stmt = $this->db->prepare("SELECT * FROM polozka_protokolu
                                    LEFT JOIN typ_opravy USING(typ_opravy_key)
                                    WHERE polozka_protokolu_key = :id");
                    $stmt->bindValue(':id', $data['polozka_protokolu_key']);
                    $stmt->execute();
                } catch (Exception $ex) {
                    $this->logger->error($ex->getMessage());
                    die($ex->getMessage());
                }
                $tplVars['polozka'] = $stmt->fetch();
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
        try {
            $stmt = $this->db->prepare("SELECT * FROM material");
            $stmt->execute();
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            die($ex->getMessage());
        }
        $tplVars['materials'] = $stmt->fetchAll();
        try {
            $stmt = $this->db->prepare("SELECT * FROM polozka_protokolu
                                    LEFT JOIN typ_opravy USING(typ_opravy_key)
                                    WHERE polozka_protokolu_key = :id");
            $stmt->bindValue(':id', $data['polozka_protokolu_key']);
            $stmt->execute();
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            die($ex->getMessage());
        }
        $tplVars['polozka'] = $stmt->fetch();
        return $this->view->render($response, 'add-protocol-cinnost.latte', $tplVars);
    }
});