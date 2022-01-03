<?php

namespace App\Employee;

use App\Common\Database;

class EmployeeAPI {

	public function __construct() {
		$this->db = Database::connect();
	}

	public function getEmployee($filter) {
		return Database::rows(
      $this->db,
			"SELECT TOP 50
			CODEMPID,
			EMPNAME,
			EMPLASTNAME
			FROM [HRTRAINING].[dbo].[EMPLOYEE]  
			WHERE $filter
			AND STATUS in (1,3)"
    );
	}
}