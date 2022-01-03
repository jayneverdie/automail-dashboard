<?php

namespace App\Status;

use App\Status\StatusAPI;

class StatusController
{
	public function __construct() {
		$this->status = new StatusAPI;
	}

	public function getAll($request, $response, $args) {
		return $response->withJson($this->status->getAll());
	}
}