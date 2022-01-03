<?php

namespace App\Commercial;

use App\Common\View;
use App\Commercial\CommercialAPI;
use App\Common\Datatables;

class CommercialController {

	public function __construct() {
		$this->view = new View;
		$this->commercial = new CommercialAPI;
		$this->datatables = new Datatables;
	}

	public function all($request, $response, $args) {
		return $this->view->render('pages/commercial/all');
	}

	public function getLogs($request, $response, $args) {

	    $parsedBody = $request->getParsedBody();

	    $data = $this->commercial->getLogs($this->datatables->filter($parsedBody));
	    $pack = $this->datatables->get($data, $parsedBody);

	    return $response->withJson($pack);
	}

}