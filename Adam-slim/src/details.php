<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/details', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    if (empty($id)){
        $person = $request->getParsedBody();
        $id = $person['id'];
        echo 'prezdne' .$id;
    }

    try {
        $stmt = $this->db->prepare('SELECT * FROM person
                                    WHERE id_person = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['person'] = $stmt->fetchAll();
    try {
        $stmt = $this->db->prepare('SELECT * FROM location
                                    WHERE id_location IN 
                                      (SELECT id_location FROM person 
                                          WHERE id_person = :id)'
        );
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['locations'] = $stmt->fetchAll();


    try {
        $stmt = $this->db->prepare('SELECT * FROM contact 
                                    LEFT JOIN contact_type ON 
                                    contact.id_contact_type = contact_type.id_contact_type
                                    WHERE id_person = :id
                                    ORDER BY name');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['contacts'] = $stmt->fetchAll();





    try {
        $stmt = $this->db->prepare('select relation.id_relation, id_person, first_name, last_name,nickname, description,
                                    relation.id_relation_type, rel.name, id_person1, id_person2 from person
                                    join relation on (person.id_person = relation.id_person1)
                                    or (person.id_person = relation.id_person2)
                                    LEFT JOIN(
                                    SELECT id_relation_type, name
                                    FROM relation_type
                                    GROUP BY id_relation_type
                                    ) AS rel USING (id_relation_type)
                                    where ((id_person1 = :id) or (id_person2 = :id)) and (id_person <> :id)
                                    order by relation.id_relation_type, first_name');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['relationships'] = $stmt->fetchAll();

    $tplVars['id'] = $id;
    return $this->view->render($response, 'details.latte', $tplVars);
})->setName('details');