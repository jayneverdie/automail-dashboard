<?php

namespace App\TireGroup_booking_revise;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class TgrAPI {

	public function __construct() {
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
		$this->automail = new Automail;
	}

	public function isSurrender($file) {
		try {
			if (preg_match("/-SURRENDER/i", $file)) {
				return true;
			} else {
				return false;
			}
		} catch (\Exception $e) {
			throw new \Exception('Error: File in correct.');
		}
	}







	public function getLogs($filter) {
		try {
			$data = Database::rows(
				$this->db_live,
				"SELECT TOP 50
				[Message],
				CustomerCode,
				[FileName],
				SendDate
				FROM Logs
				WHERE ProjectID = 18
				AND $filter
				ORDER BY ID DESC"
			);
			return $data;
		} catch (\Exception $e) {
			return [];
		}
	}
}
