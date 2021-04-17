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
                                            LEFT JOIN spotreba_materialu USING (spotreba_materialu_key)
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
        $stmt = $this->db->prepare('SELECT typ_opravy.nazov AS typ_opravy_nazov, material.nazov AS material_nazov, * FROM polozka_protokolu
                                    LEFT JOIN typ_opravy USING (typ_opravy_key)
                                    LEFT JOIN spotreba_materialu USING (spotreba_materialu_key)
                                    LEFT JOIN skladova_karta USING (skladova_karta_key)
                                    LEFT JOIN material USING (material_key)
                                    WHERE protokol_key = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['protocolItems'] = $stmt->fetchAll();

    $test = array(
        array(
        "popis" => "popis prace",
        "cena_spolu" => "cena spolu czk", //TODO niekde doplnit cenu za pracu
        "polozky" => array(
            array(
                "material" => "nazov mat 1",
                "ks" => "5",
                "cena"=> "5000"
                ),
            array(
                "material" => "nazov mat 2",
                "ks" => "5",
                "cena"=> "5000"
            )
            )
        ),
        array(
            "popis" => "popis prace2",
            "cena_spolu" => "cena spolu czk",
            "polozky" => array(
                array(
                    "material" => "nazov mat 1",
                    "ks" => "5",
                    "cena"=> "5000"
                ),
                array(
                    "material" => "nazov mat 2",
                    "ks" => "5",
                    "cena"=> "5000"
                )
            )
        )
    );

    $tplVars['test'] = $test;

    return $this->view->render($response, 'details-protocol.latte', $tplVars);
})->setName('details-protocol');