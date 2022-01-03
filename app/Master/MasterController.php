<?php

namespace App\Master;

use App\Master\MasterAPI;
use App\Master\MasterTable;
use App\Common\View;


class MasterController
{
  public function __construct() {
    $this->master = new MasterAPI;
    $this->view = new View;
    $this->master_table = new MasterTable;
  }

  public function emailCategory($request, $response, $args) {
    return $this->view->render('pages/master/email_category');
  }

  public function getEmailCategory($request, $response, $args) {
    return $response->withJson(['data' => $this->master->getEmailCategory()]);
  }

  public function createEmailCategory($request, $response, $args) {
    $parsedBody = $request->getParsedBody();

    $create = $this->master->createEmailCategory(
      htmlspecialchars($parsedBody['email_category'])
    );

    return $response->withJson($create);
  }

  public function updateEmailCategory($request, $response, $args) {
    $parsedBody = $request->getParsedBody();

    $result = $this->master->updateEmailCategory(
      $this->master_table->field[$parsedBody['name']],
      $parsedBody['pk'], 
      $parsedBody['value'],
      $this->master_table->table
    );

    return $response->withJson($result);
  }




}

