<?php

namespace App\Role;

use App\Role\RoleAPI;
use App\Common\View;
use App\Common\Message;
use App\Role\RoleTable;
use App\Capability\CapabilityAPI;
use App\Common\Datatables;

class RoleController
{
	public function __construct() {
		$this->role = new RoleAPI;
		$this->role_table = new RoleTable;
		$this->view = new View;
    $this->message = new Message;
    $this->capability = new CapabilityAPI;
    $this->datatables = new Datatables;
	}

	public function role() {
		return $this->view->render('pages/user/role');
	}
	
	public function all($request, $response, $args) {
    
    $parsedBody = $request->getParsedBody();

    $data = $this->role->all($this->datatables->filter($parsedBody));
    $pack = $this->datatables->get($data, $parsedBody);

		return $response->withJson($pack);
	}

	public function update($request, $response, $args) {
    $parsedBody = $request->getParsedBody();

    $result = $this->role->update(
      $this->role_table->field[$parsedBody['name']],
      $parsedBody['pk'],
      $parsedBody['value'],
      $this->role_table->table
    );

    return $response->withJson($result);
	}
	
	public function getRoleActive($request, $response, $args) {
		return $response->withJson($this->role->getRoleActive());
	}

  public function updateCapability($request, $response, $args) {
    $parsedBody = $request->getParsedBody();

    if ( count($parsedBody['cap_id']) === 0 ) {
      return $this->message->result(false, 'Capability not found!');
    }

    $result = $this->capability->updateCapability(
      $parsedBody['role_id'],
      $parsedBody['cap_id']
    );

    return $response->withJson($result);
  }
  
  public function create($request, $response, $args) {
    $parsedBody = $request->getParsedBody();

    if ( trim($parsedBody['role_name']) === '' ) {
      return $response->withJson($this->message->result(false, 'Data must not be blank!'));
    }

    $result = $this->role->create(
      $parsedBody['role_name']
    );

    return $response->withJson($result);
  }

  public function getMenu($request, $response, $args) {
    return $response->withJson($this->role->getMenu());
  }
  
}