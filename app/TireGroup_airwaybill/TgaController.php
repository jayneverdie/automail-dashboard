<?php

namespace App\TireGroup_airwaybill;

use App\Common\View;
use App\TireGroup_airwaybill\TgaAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;

class TgaController {

	public function __construct() {
		$this->view = new View;
		$this->Tga = new TgaAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}

	public function all($request, $response, $args) {
		return $this->view->render('pages/TireGroup_airwaybill/all');
	}

	public function getLogs($request, $response, $args) {
		try {
			$parsedBody = $request->getParsedBody();
			$data = $this->Tga->getLogs($this->datatables->filter($parsedBody));
			$pack = $this->datatables->get($data, $parsedBody);

			return $response->withJson($pack);
		} catch (\Exception $e) {
			return ['error' => $e->getMessage()];
		}
	}




}
