<?php

namespace App\Project;

//use App\Project\ProjectAPI;
use App\Project\ProjectTable;
use App\Common\View;
use App\Common\Datatables;


class ProjectController
{
  private $menu = null;

  public function __construct() {
  //  $this->Project = new ProjectAPI;
    $this->view = new View;
    $this->Project_table = new ProjectTable;
    $this->datatables = new Datatables;
    $this->Project1 = new ProjectAPI;
  }

  public function Master($request, $response, $args) {
    return $this->view->render('pages/projectmaster');
  }

  public function getEmailLists($request, $response, $args) {
    return $response->withJson(['data' => $this->Project1->getEmailLists()]);
  }

  // public function updateProjectname($request, $response, $args) {
  //   $parsedBody = $request->getParsedBody();
  //
  //     $result = $this->email->update(
  //       $this->email_lists_table->field[$parsedBody['name']],
  //       $parsedBody['pk'],
  //       $parsedBody['value'],
  //       $this->email_lists_table->table
  //     );
  //
  //     return $response->withJson($result);
  // }

  public function createProject($request, $response, $args) {

    $parsedBody = $request->getParsedBody();

    $create = $this->Project1->createProject(
      htmlspecialchars($parsedBody['user_p'])

    );

    return $response->withJson($create);
  }

  public function deleteProject($request, $response, $args) {
	    $parsedBody = $request->getParsedBody();

	    if ( trim($parsedBody['id']) === '' ) {
	      return $response->withJson($this->message->result(false, 'Data must not be blank!'));
	    }

	    $result = $this->Project1->deleteProject(
	      $parsedBody['id']
	    );

	    return $response->withJson($result);
	}

  public function updateProjectname($request, $response, $args) {
    $parsedBody = $request->getParsedBody();

      $result = $this->Project1->update(
        $this->Project_table->field[$parsedBody['name']],
        $parsedBody['pk'],
        $parsedBody['value'],
        $this->Project_table->table
      );
      return $response->withJson($result);
  }




}
