<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/details-protocol', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    $tplVars['id'] = $id;
    try {
        $stmt = $this->db->prepare('SELECT protokol.*, 
                                           zamestnanec.meno AS zamestnanec_meno, zamestnanec.priezvisko AS zamestnanec_priezvisko, zamestnanec.*, 
                                           rezervacia.*, 
                                           pozicia.*,        
                                           zakaznik.meno AS zakaznik_meno, zakaznik.priezvisko AS zakaznik_priezvisko, zakaznik.*, 
                                           prot_pol.poc_poloziek, prot_pol.cena_prace, prot_pol.cena_spotreby, (cena_prace + cena_spotreby) AS cena_spolu 
                                    FROM protokol
                                    LEFT JOIN zamestnanec USING (zamestnanec_key)
                                    LEFT JOIN pozicia USING (pozicia_key)
                                    LEFT JOIN rezervacia USING (rezervacia_key)
                                    LEFT JOIN zakaznik USING (zakaznik_key)
                                    
                                    LEFT JOIN (
                                            SELECT protokol_key, COUNT(*) AS poc_poloziek, SUM(cena_za_pracu) AS cena_prace, 
                                               SUM(spotreba_materialu.mnozstvo * predajna_cena) AS cena_spotreby
                                        FROM polozka_protokolu
                                            
                                            LEFT JOIN typ_opravy USING (typ_opravy_key)
                                            LEFT JOIN spotreba_materialu USING (polozka_protokolu_key)
                                            LEFT JOIN skladova_karta USING (skladova_karta_key)
                                            LEFT JOIN material USING (material_key)
                                        
                                        GROUP BY protokol_key
                                    ) AS prot_pol
                                    USING(protokol_key)
                                    WHERE protokol_key = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['protocol'] = $stmt->fetchAll();
    try {
        $stmt = $this->db->prepare('SELECT typ_opravy.nazov AS typ_opravy_nazov, (cena_materialu_spolu + cena_za_pracu) AS celkova_cena_polozky, * FROM polozka_protokolu
                                    LEFT JOIN typ_opravy USING (typ_opravy_key)
                                    LEFT JOIN (SELECT polozka_protokolu_key, COUNT(*) AS poc_spotr, SUM((mnozstvo * predajna_cena)) AS cena_materialu_spolu FROM spotreba_materialu
                                               LEFT JOIN skladova_karta USING (skladova_karta_key)
                                               LEFT JOIN material USING (material_key)
                                               GROUP BY polozka_protokolu_key
                                    ) AS spot_mat
                                    USING (polozka_protokolu_key)
                                    WHERE protokol_key = :id');
//        $stmt = $this->db->prepare('SELECT typ_opravy.nazov AS typ_opravy_nazov, material.nazov AS material_nazov, * FROM polozka_protokolu
//                                    LEFT JOIN typ_opravy USING (typ_opravy_key)
//                                    LEFT JOIN spotreba_materialu USING (polozka_protokolu_key)
//                                    LEFT JOIN skladova_karta USING (skladova_karta_key)
//                                    LEFT JOIN material USING (material_key)
//                                    WHERE protokol_key = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['protocolItems'] = $stmt->fetchAll();

    $items = $tplVars['protocolItems'];
    $i = 0;
    foreach ($items as $item) {
        try {
            $stmt = $this->db->prepare("SELECT *, concat(cast(mnozstvo as varchar), ' ', merna_jednotka) as mnozstvo_s_mern, (mnozstvo * predajna_cena) AS cena_materialu_spolu FROM polozka_protokolu
                                        LEFT JOIN spotreba_materialu USING (polozka_protokolu_key)
                                        LEFT JOIN skladova_karta USING (skladova_karta_key)
                                        LEFT JOIN material USING (material_key)
                                        WHERE polozka_protokolu_key = :id");
            $stmt->bindValue(':id', $item['polozka_protokolu_key']);
            $stmt->execute();
        } catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            die($ex->getMessage());
        }
        $tplVars['protocolItems'][$i]['material'] = $stmt->fetchAll();
        $i = $i + 1;
    }
//    $tplVars['i'] = 0;

    return $this->view->render($response, 'details-protocol.latte', $tplVars);
})->setName('details-protocol');

//add-material-to-protokol-item

$app->post('/delete-protocol-item-material', function (Request $request, Response $response, $args){
    $id = $request->getQueryParam('id');
    try{
        $stmt = $this->db->prepare('DELETE FROM spotreba_materialu WHERE spotreba_materialu_key=:id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex){
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    return $response->withHeader('Location', $this->router->pathFor('protocols'));
})->setName('delete-protocol-item-material');