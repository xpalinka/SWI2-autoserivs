<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/add-person', function (Request $request, Response $response, $args) {
    $tplVars['form'] = ['ln' => '', 'fn' => '', 'nn' => '', 'd'=> '', 'h' => '',];
    return $this->view->render($response, 'add-person.latte', $tplVars);
})->setName('addPerson');

$app->post('/add-person', function (Request $request, Response $response, $args) {
    $data = $request->getParsedBody();  //$_POST
    if (!empty($data['fn']) && !empty($data['nn']) && !empty($data['ln'])) {
        try {
            $stmt = $this->db->prepare('INSERT INTO person
                              (gender, last_name, first_name, nickname, birth_day, height)
                              VALUES
                              (:g, :ln, :fn, :nn , :d, :h)');
            $g = empty($data['g']) ? null : $data['g'];
            $stmt->bindValue(':g', $g);
            $stmt->bindValue(':fn', $data['fn']);
            $stmt->bindValue(':ln', $data['ln']);
            $stmt->bindValue(':nn', $data['nn']);
            $stmt->bindValue(':d', empty($data['d']) ? null : $data ['d']);
            $stmt->bindValue(':h', empty($data['h']) ? null : $data ['h']);
            $stmt->execute();
        } catch (Exception $ex) {
            if ($ex->getCode() == 23505) {
                $tplVars['error'] = 'Tato osoba uz existuje.';
                $tplVars['form'] = $data;
                return $this->view->render($response, 'add-person.latte', $tplVars);
            } else {
                $this->logger->error($ex->getMessage());
                die($ex->getMessage());
            }
        }
        return $response->withHeader('Location', $this->router->pathFor('persons'));
    } else {
        $tplVars['error'] = 'Nejsou vyplneny vsechny udaje.';
        $tplVars['form'] = $data;
        return $this->view->render($response, 'add-person.latte', $tplVars);
    }
});

$app->get('/', function (Request $request, Response $response, $args) {
    $q = $request->getQueryParam('q');

    try {
        if (empty($q)) {
            $stmt = $this->db->prepare('SELECT person.*, location.*,
                       p_sch.cnt AS meeting_count,
                       p_kont.cnt AS contact_count
                FROM person
                LEFT JOIN location USING (id_location)
                LEFT JOIN (
                  SELECT id_person, COUNT(*) AS cnt
                  FROM contact
                  GROUP BY id_person
                ) AS p_kont
                USING(id_person)
                LEFT JOIN (
                  SELECT id_person, COUNT(*) AS cnt
                  FROM person_meeting
                  GROUP BY id_person
                ) AS p_sch
                USING(id_person)
                ORDER BY last_name');
        } else {
            $stmt = $this->db->prepare('SELECT person.*, location.*,
                       p_sch.cnt AS meeting_count,
                       p_kont.cnt AS contact_count
                FROM person
                LEFT JOIN location USING (id_location)
                LEFT JOIN (
                  SELECT id_person, COUNT(*) AS cnt
                  FROM contact
                  GROUP BY id_person
                ) AS p_kont
                USING(id_person)
                LEFT JOIN (
                  SELECT id_person, COUNT(*) AS cnt
                  FROM person_meeting
                  GROUP BY id_person
                ) AS p_sch
                USING(id_person)
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


    $tplVars['people'] = $stmt->fetchAll();
    $tplVars['q'] = $q;

    return $this->view->render($response, 'persons.latte', $tplVars
    );
})->setName('persons');

/**
 * smazani osoby
 */
$app->post('/delete-person', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');

    try {
        $stmt = $this->db->prepare('DELETE FROM person
                                    WHERE id_person = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }

    return $response->withHeader('Location', $this->router->pathFor('persons'));
})->setName('deletePerson');        //{link deletePerson}


/**
 * editace
 */
$app->get('/edit-person', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    try {
        $stmt = $this->db->prepare('SELECT * FROM person
                                WHERE id_person = :id');
        $stmt->bindValue(':id', $id);
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $person = $stmt->fetch();
    $tplVars['form'] = [
        'ln' => $person['last_name'],
        'fn' => $person['first_name'],
        'nn' => $person['nickname'],
        'g' => $person['gender'],
        'h' => $person['height'],
        'd' => $person['birth_day']
    ];
    $tplVars['id'] = $id;
    return $this->view->render($response, 'edit-person.latte', $tplVars);
})->setName('editPerson');

/**
 * ulozeni zmen
 */
$app->post('/edit-person', function (Request $request, Response $response, $args) {
    $id = $request->getQueryParam('id');
    $data = $request->getParsedBody();

    if (!empty($data['fn']) && !empty($data['nn']) && !empty($data['ln'])) {
        try {
            $stmt = $this->db->prepare('UPDATE person SET
                              height = :h,
                              birth_day = :d,
                              gender = :g,
                              last_name = :ln,
                              first_name = :fn,
                              nickname = :nn
                              WHERE id_person = :id');
            $h = empty($data['h']) ? null : $data['h'];
            $d = empty($data['d']) ? null : $data['d'];
            $g = empty($data['g']) ? null : $data['g'];
            $stmt->bindValue(':h', $h);
            $stmt->bindValue(':d', $d);
            $stmt->bindValue(':g', $g);
            $stmt->bindValue(':fn', $data['fn']);
            $stmt->bindValue(':ln', $data['ln']);
            $stmt->bindValue(':nn', $data['nn']);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
        } catch (Exception $ex) {
            if ($ex->getCode() == 23505) {
                $tplVars['error'] = 'Tato osoba uz existuje.';
                $tplVars['form'] = $data;
                return $this->view->render($response, 'edit-person.latte', $tplVars);
            } else {
                $this->logger->error($ex->getMessage());
                die($ex->getMessage());
            }
        }
        return $response->withHeader('Location', $this->router->pathFor('details'));
    } else {
        $tplVars['error'] = 'Nejsou vyplneny vsechny udaje.';
        $tplVars['form'] = $data;
        return $this->view->render($response, 'edit-person.latte', $tplVars);
    }
});

$app->get('/add-with-address', function (Request $request, Response $response, $args) {
    try {
        $stmt = $this->db->query('SELECT * FROM location ORDER BY city');
        $tplVars['locations'] = $stmt->fetchAll();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['form'] = [
        'ln' => '',
        'fn' => '',
        'nn' => '',
        'g' => '',
        'b' => '',
        'h' => '',
        'ci' => '',
        'st' => '',
        'sn' => '',
        'zip' => '',
        'idl' => '',
        'c' => '',
        'n' => '',

    ];
    return $this->view->render($response, 'add-with-address.latte', $tplVars);
})->setName('addWithAddress');


$app->post('/add-with-address', function (Request $request, Response $response, $args) {
    try {
        $stmt = $this->db->query('SELECT * FROM location ORDER BY city');
        $tplVars['locations'] = $stmt->fetchAll();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $data = $request->getParsedBody();  //$_POST
    if (!empty($data['fn']) && !empty($data['nn']) && !empty($data['ln'])) {
        try {
            $this->db->beginTransaction();

            $idLocation = $data['idl'];
            if (empty($idLocation)) {
                if ((empty($data['ci']) && empty($data['c']))) {
                    $tplVars['error'] = 'Vypln minimalne mesto alebo krajinu.';
                    $tplVars['form'] = $data;
                    return $this->view->render($response, 'add-with-address.latte', $tplVars);
                } else {
                    try {
                        try {
                            $stmt = $this->db->prepare('INSERT INTO location
                                          (city, street_name, street_number, zip, country, name)
                                          VALUES
                                          (:ci, :st, :sn, :zip, :c, :n)');
                            $stmt->bindValue(':ci', empty($data['ci']) ? null : $data ['ci']);
                            $stmt->bindValue(':st', empty($data['st']) ? null : $data['st']);
                            $stmt->bindValue(':sn', empty($data['sn']) ? null : $data['sn']);
                            $stmt->bindValue(':zip', empty($data['zip']) ? null : $data['zip']);
                            $stmt->bindValue(':c', empty($data['c']) ? null : $data['c']);
                            $stmt->bindValue(':n', empty($data['n']) ? null : $data['n']);
                            $stmt->execute();
                            $idLocation = $this->db->lastInsertId('location_id_location_seq');
                        } catch (Exception $ex) {
                            $this->db->rollback();

                            if ($ex->getCode() == 23505) {
                                $tplVars['error'] = 'Tato lokacia uz existuje.';
                                $tplVars['form'] = $data;
                                return $this->view->render($response, 'add-with-address.latte', $tplVars);
                            } else {
                                $this->logger->error($ex->getMessage());
                                die($ex->getMessage());
                            }
                        }

                        $stmt = $this->db->prepare('INSERT INTO person
                              (id_location, gender, last_name, first_name, nickname, height, birth_day)
                              VALUES
                              (:idl, :g, :ln, :fn, :nn, :h, :b)');
                        $g = empty($data['g']) ? null : $data['g'];
                        $stmt->bindValue(':idl', $idLocation);
                        $stmt->bindValue(':g', $g);
                        $stmt->bindValue(':fn', $data['fn']);
                        $stmt->bindValue(':ln', $data['ln']);
                        $stmt->bindValue(':nn', $data['nn']);
                        $stmt->bindValue(':h', empty($data['h']) ? null : $data['h']);
                        $stmt->bindValue(':b', empty($data['b']) ? null : $data['b']);
                        $stmt->execute();
                    } catch (Exception $ex) {
                        $this->db->rollback();

                        if ($ex->getCode() == 23505) {
                            $tplVars['error'] = 'Tato osoba uz existuje.';
                            $tplVars['form'] = $data;
                            return $this->view->render($response, 'add-with-address.latte', $tplVars);
                        } else {
                            $this->logger->error($ex->getMessage());
                            die($ex->getMessage());
                        }
                    }
                    $this->db->commit();
                    return $response->withHeader('Location', $this->router->pathFor('persons'));
                }
            } else {
                $stmt = $this->db->prepare('INSERT INTO person
                              (id_location, gender, last_name, first_name, nickname, height, birth_day)
                              VALUES
                              (:idl, :g, :ln, :fn, :nn, :h, :b)');
                $g = empty($data['g']) ? null : $data['g'];
                $stmt->bindValue(':idl', $idLocation);
                $stmt->bindValue(':g', $g);
                $stmt->bindValue(':fn', $data['fn']);
                $stmt->bindValue(':ln', $data['ln']);
                $stmt->bindValue(':nn', $data['nn']);
                $stmt->bindValue(':h', empty($data['h']) ? null : $data['h']);
                $stmt->bindValue(':b', empty($data['b']) ? null : $data['b']);
                $stmt->execute();
            }
            $this->db->commit();
        } catch (Exception $ex) {
            $this->db->rollback();

            if ($ex->getCode() == 23505) {
                $tplVars['error'] = 'Tato osoba uz existuje.';
                $tplVars['form'] = $data;
                return $this->view->render($response, 'add-with-address.latte', $tplVars);
            } else {
                $this->logger->error($ex->getMessage());
                die($ex->getMessage());
            }
        }
        return $response->withHeader('Location', $this->router->pathFor('persons'));
    } else {
        $tplVars['error'] = 'Nejsou vyplneny vsechny udaje.';
        $tplVars['form'] = $data;
        return $this->view->render($response, 'add-with-address.latte', $tplVars);
    }
});

