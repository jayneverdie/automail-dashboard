<?php

namespace App\ShipmentPlan;

use App\Common\View;
use App\ShipmentPlan\ShipmentPlanAPI;
use App\Common\Datatables;

class ShipmentPlanController {

	public function __construct() {
		$this->view = new View;
		$this->shipmentplan = new ShipmentPlanAPI;
		$this->datatables = new Datatables;
	}

	public function all($request, $response, $args) {
		return $this->view->render('pages/shipmentplan/all');
	}

	public function getLogs($request, $response, $args) {

	    $parsedBody = $request->getParsedBody();

	    $data = $this->shipmentplan->getLogs($this->datatables->filter($parsedBody));
	    $pack = $this->datatables->get($data, $parsedBody);

	    return $response->withJson($pack);
	}

}