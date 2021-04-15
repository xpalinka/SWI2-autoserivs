<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/protocols', function (Request $request, Response $response, $args) {

    try {
        $stmt = $this->db->prepare('SELECT protokol.*, zamestnanec.*, p_prot_pol.poc_poloziek FROM protokol
                                    LEFT JOIN zamestnanec USING (zamestnanec_key)
                                    LEFT JOIN (
                                    SELECT protokol_key, COUNT(*) AS poc_poloziek
                                    FROM polozka_protokolu
                                    GROUP BY protokol_key
                                    ) AS p_prot_pol
                                    USING(protokol_key)');
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }

    $tplVars['protocols'] = $stmt->fetchAll();

    return $this->view->render($response, 'protocols.latte', $tplVars);
})->setName('protocols');