<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/add-cinnost', function(Request $request, Response $response, $args) {

    try {
        $stmt = $this->db->prepare("SELECT * FROM material");
        $stmt->execute();
    } catch (Exception $ex) {
        $this->logger->error($ex->getMessage());
        die($ex->getMessage());
    }
    $tplVars['materials'] = $stmt->fetchAll();

        return $this->view->render($response, 'add-cinnost.latte',$tplVars);

})->setName('add-cinnost');

$app->post('/add-cinnost', function(Request $request, Response $response, $args) {
    $data = $request->getParsedBody();  //$_POST
    if(!empty($data['nc']) && !empty($data['czp'])) {
        try {
            $this->db->beginTransaction();

            $stmt=$this->db->prepare('INSERT INTO typ_opravy
                                      (cena_za_pracu, nazov) 
                                    VALUES
                                      (:czp, :nc)');
            $stmt->bindValue(':czp', $data['czp']);
            $stmt->bindValue(':nc', $data['nc']);


            $stmt->execute();
//
            $this->db->commit();

        } catch (Exception $ex) {
            $this->db->rollback();
            if($ex->getCode() == 23505) {
                print $ex->getMessage();
                $tplVars['error'] = 'Tento prtokol už existuje.';

                return $this->view->render($response, 'add-cinnost.latte', $tplVars);
            } else {
                $this->logger->error($ex->getMessage());
                die($ex->getMessage());
            }
        }
        return $response->withHeader('Location', $this->router->pathFor('protocols'));
    } else {
        $tplVars['error'] = 'Nie sú vyplnené všetky údaje.';
        $tplVars['form'] = $data;
        return $this->view->render($response, 'add-cinnost.latte', $tplVars);
    }

});