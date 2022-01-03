<?php

namespace App\pcr;

use App\Common\View;
use App\pcr\PcrAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;

class PcrController {

	public function __construct() {
		$this->view = new View;
		$this->pcr = new PcrAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}

	public function all($request, $response, $args) {
		return $this->view->render('pages/pcr/all');
	}

	public function getLogs($request, $response, $args) {
		try {
			$parsedBody = $request->getParsedBody();
			$data = $this->pcr->getLogs($this->datatables->filter($parsedBody));
			$pack = $this->datatables->get($data, $parsedBody);

			return $response->withJson($pack);
		} catch (\Exception $e) {
			return ['error' => $e->getMessage()];
		}
	}




}
