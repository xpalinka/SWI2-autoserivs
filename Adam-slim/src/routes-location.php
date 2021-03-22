<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/all-locations', function (Request $request, Response $response, $args) {
    $q = $request->getQueryParam('q');

    try {
        if (empty($q)) {
            $stmt = $this->db->prepare('SELECT * FROM location
                                        ORDER BY country,city,street_name,street_number');
        } else {
            $stmt = $this->db->prepare('SELECT * FROM location
                                        WHERE city ILIKE :q
                                        OR street_name ILIKE :q
                                        OR country ILIKE :q
                                        OR name ILIKE :q
                                        
                                        ORDER BY country,city,street_name,street_number');
            $stmt->bindValue(':q', $q . '%');
        }
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }


    $tplVars['locations'] = $stmt->fetchAll();
    $tplVars['q'] = $q;

    return $this->view->render($response, 'all-locations.latte', $tplVars);
})->setName('allLocations');

$app->get('/add-location', function (Request $request, Response $response, $args) {
    $tplVars['form'] = [
        'ci' => '',
        's_name' => '',
        's_number' => '',
        'zip' => '',
        'country' => '',
        'name' => '',
        'lat' => '',
        'long' => '',
    ];
    return $this->view->render($response, 'add-location.latte', $tplVars);
})->setName('addLocation');

$app->post('/add-location', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    $data = $request->getParsedBody();  //$_POST
    if (!empty($data['ci']) or !empty($data['country'] or (!empty($data['lat']) && !empty($data['long'])))) {
        try {
            $stmt = $this->db->prepare('INSERT INTO location
                              (city, street_name, street_number, zip,country,name,latitude,longitude)
                              VALUES
                              (:ci, :s_name, :s_number, :zip, :country, :name,:lat, :long)');
            $stmt->bindValue(':ci', empty($data['ci']) ? null : $data['ci']);
            $stmt->bindValue(':s_name', empty($data['s_name']) ? null : $data['s_name']);
            $stmt->bindValue(':s_number', empty($data['s_number']) ? null : $data['s_number']);
            $stmt->bindValue(':zip', empty($data['zip']) ? null : $data['zip']);
            $stmt->bindValue(':country', empty($data['country']) ? null : $data['country']);
            $stmt->bindValue(':name', empty($data['name']) ? null : $data['name']);
            $stmt->bindValue(':lat', empty($data['lat']) ? null : $data['lat']);
            $stmt->bindValue(':long', empty($data['long']) ? null : $data['long']);
            $stmt->execute();
        } catch (Exception $ex) {
            if ($ex->getCode() == 23505) {
                $tplVars['error'] = 'Tato lokacia uz existuje.';
                $tplVars['form'] = $data;
                return $this->view->render($response, 'add-location.latte', $tplVars);
            } else {
                $this->logger->error($ex->getMessage());
                die($ex->getMessage());
            }
        }
        if (empty($id)){
            return $response->withHeader('Location', $this->router->pathFor('allLocations'));
        }else {
            $tplVars['id'] = $id;
            return $response->withHeader('Location', $this->router->pathFor('details'));
        }

    } else {
        $tplVars['error'] = 'Zadaj podrobnejsie udaje.';
        $tplVars['form'] = $data;
        return $this->view->render($response, 'add-location.latte', $tplVars);
    }
});

/**pouzivana v details.php*/
//$app->get('/location', function(Request $request, Response $response, $args) {
//    $id = $request->getQueryParam('id');
//    try {
//        $stmt = $this->db->prepare('SELECT * FROM location
//                                    WHERE id_location IN
//                                      (SELECT id_location FROM person
//                                          WHERE id_person = :id)'
//        );
//        $stmt->bindValue(':id', $id);
//        $stmt->execute();
//    } catch (Exception $ex) {
//        $this->logger->error($ex->getMessage());
//        die($ex->getMessage());
//    }
//    $tplVars['locations'] = $stmt->fetchAll();
//    $tplVars['id'] = $id;
//
//    return $this->view->render($response, 'location.latte', $tplVars);
//})->setName('location');

/**
 * smazani (odobranie) lokacie
 * nefunguje proste to neviem nastavit na nul neviem preco
 */
$app->post('/delete-location', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    try {
        $stmt = $this->db->prepare('UPDATE person SET
                              id_location = NULL
                              WHERE id_person = :id');
        //$stmt->bindValue(':idl', $idl);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }


    return $response->withHeader('Location', $this->router->pathFor('allLocations'));
})->setName('deleteLocation');


$app->get('/add-to-location', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    try {
        $stmt = $this->db->query('SELECT * FROM location ORDER BY city');
        $tplVars['locations'] = $stmt->fetchAll();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['form'] = [
        'idl' => '',
    ];
    $tplVars['id'] = $id;
    return $this->view->render($response, 'add-to-location.latte', $tplVars);
})->setName('addToLocation');

/**
 * postova metoda nefunguje chyba id person
 */
$app->post('/add-to-location', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    $data = $request->getParsedBody();
    try {
        $stmt = $this->db->query('SELECT * FROM location ORDER BY city');
        $tplVars['locations'] = $stmt->fetchAll();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }


    if (!empty($data['idl'])) {
        try {

            $stmt = $this->db->prepare('UPDATE person SET
                              id_location = :idl
                              WHERE id_person = :id');
            $stmt->bindValue(':idl', $data['idl']);
            $stmt->bindValue(':id', $id);
            $stmt->execute();

        } catch (Exception $ex) {
            if ($ex->getCode() == 23505) {
                $tplVars['error'] = 'Tento kontakt uz existuje.';
                $tplVars['form'] = $data;
                return $this->view->render($response, 'add-to-location.latte', $tplVars);
            } else {
                $this->logger->error($ex->getMessage());
                die($ex->getMessage());
            }
        }
        return $response->withHeader('Location', $this->router->pathFor('persons'));
    } else {
        $tplVars['error'] = 'Zadaj lokaciu.';
        $tplVars['form'] = $data;
        return $this->view->render($response, 'add-to-location.latte', $tplVars);
    }
});
