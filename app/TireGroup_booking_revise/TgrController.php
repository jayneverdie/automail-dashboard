<?php

namespace App\TireGroup_booking_revise;

use App\Common\View;
use App\TireGroup_booking_revise\TgrAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;

class TgrController {

	public function __construct() {
		$this->view = new View;
		$this->Tgr = new TgrAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}

	public function all($request, $response, $args) {
		return $this->view->render('pages/TireGroup_booking_revise/all');
	}

	public function getLogs($request, $response, $args) {
		try {
			$parsedBody = $request->getParsedBody();
			$data = $this->Tgr->getLogs($this->datatables->filter($parsedBody));
			$pack = $this->datatables->get($data, $parsedBody);

			return $response->withJson($pack);
		} catch (\Exception $e) {
			return ['error' => $e->getMessage()];
		}
	}




}
