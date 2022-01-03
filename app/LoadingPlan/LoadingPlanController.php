<?php

namespace App\LoadingPlan;

use App\Common\View;
use App\LoadingPlan\LoadingPlanAPI;
use App\Common\Datatables;

class LoadingPlanController {

	public function __construct() {
		$this->view = new View;
		$this->loadingplan = new LoadingPlanAPI;
		$this->datatables = new Datatables;
	}

	public function all($request, $response, $args) {
		return $this->view->render('pages/loadingplan/all');
	}

	public function getLogs($request, $response, $args) {

	    $parsedBody = $request->getParsedBody();

	    $data = $this->loadingplan->getLogs($this->datatables->filter($parsedBody));
	    $pack = $this->datatables->get($data, $parsedBody);

	    return $response->withJson($pack);
	}

}