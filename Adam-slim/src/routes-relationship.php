<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/all-relationships', function (Request $request, Response $response, $args) {
    $q = $request->getQueryParam('q');

    try {
        if (empty($q)) {
            $stmt = $this->db->prepare('SELECT id_relation,p1.first_name as fno1,p1.last_name as lno1,p1.nickname as nno1, p2.first_name as fno2,p2.last_name as lno2,p2.nickname as nno2, name, description FROM relation 
                                        LEFT JOIN relation_type ON
                                        relation.id_relation_type = relation_type.id_relation_type
                                        LEFT JOIN person as p1 ON
                                        relation.id_person1 = p1.id_person
                                        LEFT JOIN person as p2 ON 
                                        relation.id_person2 = p2.id_person');

        } else {
            $stmt = $this->db->prepare('SELECT id_relation,p1.first_name as fno1,p1.last_name as lno1,p1.nickname as nno1, p2.first_name as fno2,p2.last_name as lno2,p2.nickname as nno2, name, description FROM relation 
                                        LEFT JOIN relation_type ON
                                        relation.id_relation_type = relation_type.id_relation_type
                                        LEFT JOIN person as p1 ON
                                        relation.id_person1 = p1.id_person
                                        LEFT JOIN person as p2 ON 
                                        relation.id_person2 = p2.id_person
                                        WHERE p1.nickname ILIKE :q 
                                        OR p1.first_name ILIKE :q 
                                        OR p1.last_name ILIKE :q
                                        OR p2.nickname ILIKE :q 
                                        OR p2.first_name ILIKE :q 
                                        OR p2.last_name ILIKE :q
                                        OR description ILIKE :q 
                                        OR name ILIKE :q');
            $stmt->bindValue(':q', $q . '%');
        }
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }


    $tplVars['relationships'] = $stmt->fetchAll();
    $tplVars['q'] = $q;

    return $this->view->render($response, 'all-relationships.latte', $tplVars);
})->setName('allRelationships');


$app->get('/edit-relationship', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    try {
        $stmt = $this->db->prepare('SELECT * FROM relation 
                                    LEFT JOIN relation_type ON
                                    relation.id_relation_type = relation_type.id_relation_type
                                    WHERE id_relation = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $relationships = $stmt->fetch();
    $tplVars['form'] = [
        'd' => $relationships['description'],
        'n' => $relationships['name'],
        'idrt' => $relationships['id_relation_type'],
        'nn' => '',
    ];
    try {
        $stmt = $this->db->query('SELECT * FROM relation_type ORDER BY name');
        $tplVars['relations'] = $stmt->fetchAll();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['id'] = $id;
    return $this->view->render($response, 'edit-relationship.latte', $tplVars);
})->setName('editRelationship');


$app->post('/edit-relationship', function (Request $request, Response $response, $args) {
    $idr = $request->getQueryParam('id');
//    try {
//        $stmt = $this->db->query('SELECT * FROM contact_type ORDER BY name');
//        $tplVars['contacts'] = $stmt->fetchAll();
//    } catch (Exception $ex) {
//        $this->logger->error($ex->getMessage());
//        die($ex->getMessage());
//    }
    $data = $request->getParsedBody();  //$_POST
    $idRelationType = $data['idrt'];


    try {
        $this->db->beginTransaction();


        if (empty($idRelationType)) {

            $stmt = $this->db->prepare('INSERT INTO relation_type
                              (name)
                              VALUES
                              (:n)');
            $stmt->bindValue(':n', $data['nn']);
            $stmt->execute();
            $idRelationType = $this->db->lastInsertId('relation_type_id_relation_type_seq');
        }

        $stmt = $this->db->prepare('UPDATE relation 
                                        SET id_relation_type = :idrt, description = :d
                                        WHERE id_relation = :idr');

        $stmt->bindValue(':idrt', $idRelationType);
        $stmt->bindValue(':d', empty($data['d']) ? null : $data['d']);
        $stmt->bindValue(':idr', $idr);
        $stmt->execute();
        $this->db->commit();
    } catch (Exception $ex) {
        $this->db->rollback();

        if ($ex->getCode() == 23505) {
            $tplVars['error'] = 'Tento vztah uz existuje.';
            $tplVars['form'] = $data;
            $tplVars['id'] = $idr;
            return $this->view->render($response, 'edit-relationship.latte', $tplVars);
        } else {
            $this->logger->error($ex->getMessage());
            die($ex->getMessage());
        }
    }

    return $response->withHeader('Location', $this->router->pathFor('allRelationships'));
});

/**
 * smazani vztahu
 */
$app->post('/delete-relationship', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');

    try {
        $stmt = $this->db->prepare('DELETE FROM relation
                                    WHERE id_relation = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }

    return $response->withHeader('Location', $this->router->pathFor('allRelationships'));
})->setName('deleteRelationship');


$app->get('/add-relationship', function (Request $request, Response $response, $args) {
    $idp = $request->getQueryParam('id');

    try {
        $stmt = $this->db->prepare('SELECT * FROM person
                                    WHERE id_person = :id');
        $stmt->bindValue(':id', $idp);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['user'] = $stmt->fetchAll();

    $tplVars['form'] = [
        'idp2' => '',
        'd' => '',
        'n' => '',
        'idrt' => '',
        'nn' => '',
    ];
    try {
        $stmt = $this->db->query('SELECT * FROM relation_type ORDER BY name');
        $tplVars['relations'] = $stmt->fetchAll();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }

    try {
        $stmt = $this->db->query('SELECT * FROM person ORDER BY last_name');
        $tplVars['persons'] = $stmt->fetchAll();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }

    $tplVars['id'] = $idp;

    return $this->view->render($response, 'add-relationship.latte', $tplVars);

})->setName('addRelationship');


$app->post('/add-relationship', function (Request $request, Response $response, $args) {
    $idp = $request->getQueryParam('id');
    $data = $request->getParsedBody();  //$_POST
    $idRelationType = $data['idrt'];

    try {
        $stmt = $this->db->query('SELECT * FROM relation_type ORDER BY name');
        $tplVars['relations'] = $stmt->fetchAll();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }

    try {
        $stmt = $this->db->query('SELECT * FROM person ORDER BY last_name');
        $tplVars['persons'] = $stmt->fetchAll();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }

    if (!empty($data['idp2'])) {
        if ($data['idp2'] != $idp) {

            try {
                $this->db->beginTransaction();

                if (empty($idRelationType)) {
                    try {
                        $stmt = $this->db->prepare('INSERT INTO relation_type
                                  (name)
                                  VALUES
                                  (:n)');
                        $stmt->bindValue(':n', $data['nn']);
                        $stmt->execute();
                        $idRelationType = $this->db->lastInsertId('relation_type_id_relation_type_seq');

                        $stmt = $this->db->prepare('INSERT INTO relation
                              (id_relation_type, id_person1, id_person2, description)
                              VALUES
                              (:idrt, :id1, :id2, :d)');
                        $stmt->bindValue(':idrt', $idRelationType);
                        $stmt->bindValue(':id1', $idp);
                        $stmt->bindValue(':id2', $data['idp2']);
                        $stmt->bindValue(':d', empty($data['d']) ? null : $data['d']);
                        $stmt->execute();
                    } catch (Exception $ex) {
                        $this->db->rollback();

                        if ($ex->getCode() == 23505) {
                            $tplVars['error'] = 'Tento vztah uz existuje.';
                            $tplVars['form'] = $data;
                            $tplVars['id'] = $idp;
                            return $this->view->render($response, 'add-relationship.latte', $tplVars);
                        } else {
                            $this->logger->error($ex->getMessage());
                            die($ex->getMessage());
                        }
                    }
                    
                } else {

                    $stmt = $this->db->prepare('INSERT INTO relation
                              (id_relation_type, id_person1, id_person2, description)
                              VALUES
                              (:idrt, :id1, :id2, :d)');
                    $stmt->bindValue(':idrt', $idRelationType);
                    $stmt->bindValue(':id1', $idp);
                    $stmt->bindValue(':id2', $data['idp2']);
                    $stmt->bindValue(':d', empty($data['d']) ? null : $data['d']);
                    $stmt->execute();
                }
            } catch (Exception $ex) {
                $this->db->rollback();

                if ($ex->getCode() == 23505) {
                    $tplVars['error'] = 'Tento vztah uz existuje.';
                    $tplVars['form'] = $data;
                    $tplVars['id'] = $idp;
                    return $this->view->render($response, 'add-relationship.latte', $tplVars);
                } else {
                    $this->logger->error($ex->getMessage());
                    die($ex->getMessage());
                }
            }
            $this->db->commit();
            return $response->withHeader('Location', $this->router->pathFor('allRelationships'));
        } else {
            $tplVars['error'] = 'Uzivatel nemoze byt vo vztahu sam so sebou';
            $tplVars['form'] = $data;
            $tplVars['id'] = $idp;
            return $this->view->render($response, 'add-relationship.latte', $tplVars);
        }
    } else {
        $tplVars['error'] = 'Nejsou vyplneny vsechny udaje.';
        $tplVars['form'] = $data;
        $tplVars['id'] = $idp;
        return $this->view->render($response, 'add-relationship.latte', $tplVars);
    }
});

