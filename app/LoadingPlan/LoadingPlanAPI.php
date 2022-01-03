<?php 

namespace App\LoadingPlan;

use App\Common\Database;
use Webmozart\Assert\Assert;

class LoadingPlanAPI {

	public function __construct() {
		$this->db = Database::connect();
	}

	public function getLogs($filter) {
		try {
			return Database::rows(
				$this->db,
				"SELECT TOP 50 L.ID,L.ProjectID,L.Message,L.Source,P.ProjectName,L.SendDate,L.CustomerCode,L.SO,L.QA
				FROM Logs L
				LEFT JOIN Project P ON L.ProjectID=P.ProjectID
				WHERE L.ProjectID=? AND $filter
				ORDER BY L.ID DESC",
				[
					20
				]
			);
		} catch (\Exception $e) {
			throw new \Exception('Error: Query error.');
		}
	}

}