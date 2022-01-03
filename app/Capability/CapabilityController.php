<?php

namespace App\Capability;

use App\Capability\CapabilityAPI;
use App\Common\View;
use App\Capability\CapabilityTable;
use App\Common\Datatables;

class CapabilityController 
{

	public function __construct() {
		$this->capability = new CapabilityAPI;
		$this->view = new View;
		$this->capability_table = new CapabilityTable;
		$this->datatables = new Datatables;
	}

	public function index() {
		return $this->view->render('pages/user/capability');
	}

	public function all($request, $response, $args) {
		$parsedBody = $request->getParsedBody();

		$data = $this->capability->all($this->datatables->filter($parsedBody));
		$pack = $this->datatables->get($data, $parsedBody);

		return $response->withJson($pack);
	}

	public function getActive($request, $response, $args) {
		$result = $this->capability->getActive();
		return $response->withJson($result);
	}

	public function getCapabilityByRole($request, $response, $args) {
		$result = $this->capability->getCapabilityByRole($args['role_id'], $args['category_id']);
		return $response->withJson($result);
	}

	public function getCapabilityByCategory($request, $response, $args) {
		$result = $this->capability->getCapabilityByCategory($args['category_id']);
		return $response->withJson($result);
	}

	public function update($request, $response, $args) {
		$parsedBody = $request->getParsedBody();

	    $result = $this->capability->update(
	      $this->capability_table->field[$parsedBody['name']],
	      $parsedBody['pk'],
	      $parsedBody['value'],
	      $this->capability_table->table
	    );

	    return $response->withJson($result);
	}

	public function create($request, $response, $args) {
		$parsedBody = $request->getParsedBody();

		if ( trim($parsedBody['capability_slug']) === '' || trim($parsedBody['capability_name']) === '' ) {
			return $response->withJson($this->message->result(false, 'Data must not be blank!'));
		}

    $result = $this->capability->create(
      $parsedBody['capability_slug'],
      $parsedBody['capability_name']
    );

    return $response->withJson($result);
	}

	public function delete($request, $response, $args) {
		$parsedBody = $request->getParsedBody();

		$result = $this->capability->delete(
      $parsedBody['id']
    );

    return $response->withJson($result);
	}
}