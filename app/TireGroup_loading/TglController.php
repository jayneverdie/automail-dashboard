<?php

namespace App\TireGroup_loading;

use App\Common\View;
use App\TireGroup_loading\TglAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;

class TglController {

	public function __construct() {
		$this->view = new View;
		$this->Tgl = new TglAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}

	public function all($request, $response, $args) {
		return $this->view->render('pages/TireGroup_loading/all');
	}

	public function getLogs($request, $response, $args) {
		try {
			$parsedBody = $request->getParsedBody();
			$data = $this->Tgl->getLogs($this->datatables->filter($parsedBody));
			$pack = $this->datatables->get($data, $parsedBody);

			return $response->withJson($pack);
		} catch (\Exception $e) {
			return ['error' => $e->getMessage()];
		}
	}




}
