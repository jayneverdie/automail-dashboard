<?php

namespace App\Releasedoc;

use App\Common\View;
use App\Releasedoc\ReleasedocAPI;
use App\Common\Datatables;

class ReleasedocController {

	public function __construct() {
		$this->view = new View;
		$this->releasedoc = new ReleasedocAPI;
		$this->datatables = new Datatables;
	}

	public function all($request, $response, $args) {
		return $this->view->render('pages/releasedoc/all');
	}

	public function getLogs($request, $response, $args) {

	    $parsedBody = $request->getParsedBody();

	    $data = $this->releasedoc->getLogs($this->datatables->filter($parsedBody));
	    $pack = $this->datatables->get($data, $parsedBody);

	    return $response->withJson($pack);
	}

	public function waiting($request, $response, $args) {
		return $this->view->render('pages/releasedoc/waiting');
	}

	public function getWaiting($request, $response, $args) {

	    $parsedBody = $request->getParsedBody();

	    $data = $this->releasedoc->getWaiting($this->datatables->filter($parsedBody));
	    $pack = $this->datatables->get($data, $parsedBody);

	    return $response->withJson($pack);
	}

}