<?php

namespace App\Status;

use App\Common\Database;

class StatusAPI
{
	public function __construct() {
		$this->db = Database::connect();
	}

	public function getAll() {
		return Database::rows(
			$this->db,
			"SELECT * FROM web_status"
		);
	}
}