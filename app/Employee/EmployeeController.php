<?php

namespace App\Employee;

use App\Employee\EmployeeAPI;
use App\Common\Datatables;

class EmployeeController {

	public function __construct() {
		$this->employee = new EmployeeAPI;
		$this->datatables = new Datatables;
	}

	public function getEmployee($request, $response, $args) {
		$parsedBody = $request->getParsedBody();

		$filter = $this->datatables->filter($parsedBody);
		$data = $this->employee->getEmployee($filter);
		$pack = $this->datatables->get($data, $parsedBody);

		return $response->withJson($pack);
	}
}