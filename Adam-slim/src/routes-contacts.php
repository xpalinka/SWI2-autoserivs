<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/**pouzivana v details.php*/
//$app->get('/contacts', function(Request $request, Response $response, $args) {
//    $id = $request->getQueryParam('id');
//    try {
//        $stmt = $this->db->prepare('SELECT * FROM contact
//                                    LEFT JOIN contact_type ON
//                                    contact.id_contact_type = contact_type.id_contact_type
//                                    WHERE id_person = :id
//                                    ORDER BY name');
//        $stmt->bindValue(':id', $id);
//        $stmt->execute();
//    } catch (Exception $ex) {
//        $this->logger->error($ex->getMessage());
//        die($ex->getMessage());
//    }
//    $tplVars['contacts'] = $stmt->fetchAll();
//    $tplVars['id'] = $id;
//
//    return $this->view->render($response, 'contacts.latte', $tplVars);
//})->setName('contacts');

/**
 * smazani kontaktu
 */
$app->post('/delete-contact', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');

    try {
        try {
            $stmt = $this->db->prepare('SELECT * FROM person
                                    WHERE id_person = :id');
            $stmt->bindValue(':id', $id);
            $stmt->execute();
        }catch (Exception $ex) {
            $this->logger->error($ex->getMessage());
            die($ex->getMessage());
        }
        $tplVars['person'] = $stmt->fetchAll();

        $stmt = $this->db->prepare('DELETE FROM contact
                                    WHERE id_contact = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    //$tplVars['id'] = $id;
    return $response->withHeader('Location', $this->router->pathFor('details'),$tplVars);
})->setName('deleteContact');

/**
 * editace
 */
$app->get('/edit-contact', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    try {
        $stmt = $this->db->prepare('SELECT * FROM contact
                                WHERE id_contact = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $contact = $stmt->fetch();
    $tplVars['form'] = [
        'c' => $contact['contact'],
    ];
    $tplVars['id'] = $id;
    return $this->view->render($response, 'edit-contact.latte', $tplVars);
})->setName('editContact');


$app->post('/edit-contact', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    $data = $request->getParsedBody();
    if (!empty($data['c'])) {
        try {
            $stmt = $this->db->prepare('UPDATE contact SET
                              contact = :c
                              WHERE id_contact = :id');
            $stmt->bindValue(':c', $data['c']);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
        } catch (Exception $ex) {
            if ($ex->getCode() == 23505) {
                $tplVars['error'] = 'Tento kontakt uz existuje.';
                $tplVars['form'] = $data;
                return $this->view->render($response, 'edit-contact.latte', $tplVars);
            } else {
                $this->logger->error($ex->getMessage());
                die($ex->getMessage());
            }
        }
        return $response->withHeader('Location', $this->router->pathFor('allContacts'));
    } else {
        $tplVars['error'] = 'Nejsou vyplneny vsechny udaje.';
        $tplVars['form'] = $data;
        return $this->view->render($response, 'edit-contact.latte', $tplVars);
    }
});

$app->get('/all-contacts', function (Request $request, Response $response, $args) {
    $q = $request->getQueryParam('q');

    try {
        if (empty($q)) {
            $stmt = $this->db->prepare('SELECT person.id_person,nickname,first_name,last_name,id_contact,contact.id_contact_type,contact,	
                                        contact_type.id_contact_type,name,validation_regexp FROM person
                                        LEFT JOIN contact ON
                                        person.id_person = contact.id_person
                                        LEFT JOIN contact_type ON
                                        contact.id_contact_type = contact_type.id_contact_type
                                        
                                        ORDER BY last_name');
        } else {
            $stmt = $this->db->prepare('SELECT person.id_person,nickname,first_name,last_name,id_contact,contact.id_contact_type,contact,	
                                        contact_type.id_contact_type,name,validation_regexp FROM person
                                        LEFT JOIN contact ON
                                        person.id_person = contact.id_person
                                        LEFT JOIN contact_type ON
                                        contact.id_contact_type = contact_type.id_contact_type
                                        WHERE last_name ILIKE :q
                                        OR first_name ILIKE :q
                                        OR nickname ILIKE :q
                                        
                                        ORDER BY last_name');
            $stmt->bindValue(':q', $q . '%');
        }
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }


    $tplVars['contacts'] = $stmt->fetchAll();
    $tplVars['q'] = $q;

    return $this->view->render($response, 'all-contacts.latte', $tplVars);
})->setName('allContacts');


$app->get('/add-contact', function (Request $request, Response $response, $args) {
    $idp = $request->getQueryParam('id');
    try {
        $stmt = $this->db->query('SELECT * FROM contact_type ORDER BY name');
        $tplVars['contacts'] = $stmt->fetchAll();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['form'] = [
        'c' => '',
        'id_c_type' => '',
        'n' => '',
        'r' => "",
    ];
    $tplVars['id'] = $idp;
    return $this->view->render($response, 'add-contact.latte', $tplVars);
})->setName('addContact');


$app->post('/add-contact', function (Request $request, Response $response, $args) {
    $idp = $request->getQueryParam('id');
    try {
        $stmt = $this->db->query('SELECT * FROM contact_type ORDER BY name');
        $tplVars['contacts'] = $stmt->fetchAll();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $data = $request->getParsedBody();  //$_POST
    $idContactType = $data['id_c_type'];

    if (!empty($data['c'])) {
        try {
            $this->db->beginTransaction();


            if (empty($idContactType)) {

                $stmt = $this->db->prepare('INSERT INTO contact_type
                              (name, validation_regexp)
                              VALUES
                              (:n, :r)');
                $stmt->bindValue(':n', $data['n']);
                $stmt->bindValue(':r', '');
                $stmt->execute();
                $idContactType = $this->db->lastInsertId('contact_type_id_contact_type_seq');
            }

            $stmt = $this->db->prepare('INSERT INTO contact
                              (id_contact_type, contact, id_person)
                              VALUES
                              (:id_c_type, :c, :idp)');
            $stmt->bindValue(':id_c_type', $idContactType); //$idContactTYpe
            $stmt->bindValue(':c', $data['c']);
            $stmt->bindValue(':idp', $idp);
            $stmt->execute();

        } catch (Exception $ex) {
            $this->db->rollback();

            if ($ex->getCode() == 23505) {
                $tplVars['error'] = 'Tento kontakt uz existuje.';
                $tplVars['form'] = $data;
                $tplVars['id'] = $idp;
                return $this->view->render($response, 'add-contact.latte', $tplVars);
            } else {
                $this->logger->error($ex->getMessage());
                die($ex->getMessage());
            }
        }
        $this->db->commit();
        return $response->withHeader('Location', $this->router->pathFor('persons'));

    } else {
        $tplVars['error'] = 'Nejsou vyplneny vsechny udaje.';
        $tplVars['form'] = $data;
        $tplVars['id'] = $idp;
        return $this->view->render($response, 'add-contact.latte', $tplVars);
    }
});

