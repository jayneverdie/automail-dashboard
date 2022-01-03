<?php

namespace App\Port;

use App\Common\Database;
use App\Common\Message;

class PortAPI
{
	public function __construct()
	{
		$this->db = Database::connect();
		$this->message = new Message;
	}

	public function all($filter)
	{
		return Database::rows(
			$this->db,
			"SELECT 
				ID,
				ProjectName,
				ProjectID
				FROM ProjectPort
	      WHERE $filter"
		);
	}

	public function getEmailListsPortCamso()
	{
		return Database::rows(
			$this->db,
			"SELECT
	      ProjectID,
	      ProjectName
	      FROM Project 
	      WHERE ProjectID IN (7,8,9)"
		);
	}

	public function getEmailListsPortTiregroup()
	{
		return Database::rows(
			$this->db,
			"SELECT
			ProjectID,
			ProjectName
			FROM Project
			WHERE ProjectID IN (13,14,15,16,17,18)"
		);
	}

	public function portType($customerport)
	{
		return Database::rows(
			$this->db,
			"SELECT EL.Country
			FROM EmailLists EL
			LEFT JOIN Project P ON EL.ProjectID=P.ProjectID
			WHERE P.ProjectName=?
			AND EL.Country IS NOT NULL
			GROUP BY Country",
			[$customerport]
		);
	}

	public function portEmail($filter, $port)
	{
		return Database::rows(
			$this->db,
			"SELECT EL.*,ET.Description[EmailTypeName],EC.Description[EmailCategoryName]
			FROM EmailLists EL
			LEFT JOIN Project P ON EL.ProjectID=P.ProjectID
			LEFT JOIN EmailType ET ON EL.EmailType=ET.ID
			LEFT JOIN EmailCategory EC ON EL.EmailCategory=EC.ID
			WHERE EL.Port=? AND $filter",
			[$port]
		);
	}

	public function getEmailCategory($project_id)
	{

		$ar_camso = [7, 8, 9];
		$ar_tiregroup = [13, 14, 15, 16, 17, 18];

		if (in_array($project_id, $ar_camso)) {
			$id = '16,17';
		}
		if (in_array($project_id, $ar_tiregroup)) {
			$id = '15';
		}
		return Database::rows(
			$this->db,
			"SELECT *
			FROM EmailCategory
			WHERE ID IN ($id)"
		);
	}

	public function create($email, $type, $category, $project, $port)
	{

		$create = Database::query(
			$this->db,
			"INSERT INTO EmailLists(
	        Email,
	        Country,
	        EmailType,
	        ProjectID,
	        EmailCategory,
	        Status
	      ) VALUES(?, ?, ?, ?, ?, ?)",
			[
				$email,
				$port,
				$type,
				$project,
				$category,
				1
			]
		);

		if ($create) {
			return $this->message->result(true, 'Create successful!');
		} else {
			return $this->message->result(false, 'Create failed!');
		}
	}

	public function delete($id)
	{

		$delete = Database::query(
			$this->db,
			"DELETE FROM EmailLists
			WHERE ID=?",
			[$id]
		);

		if ($delete) {
			return $this->message->result(true, 'Delete successful!');
		} else {
			return $this->message->result(false, 'Delete failed!');
		}
	}

	public function update($name, $pk, $value, $table)
	{

		$update = Database::query(
			$this->db,
			"UPDATE $table
	      SET $name = ?
	      WHERE ID = ?",
			[
				$value,
				$pk
			]
		);

		if ($update) {
			return $this->message->result(true, 'Update successful!');
		} else {
			return $this->message->result(false, 'Update failed!');
		}
	}

	public function getProject()
	{
		try {
			return Database::rows(
				$this->db,
				"SELECT 
				P.ProjectID,
				P.ProjectName
				FROM Project P
				WHERE P.UsePort = 1"
			);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function addPort($country, $port, $email, $type, $project_id, $cat, $status)
	{
		try {
			$query = Database::query(
				$this->db,
				"INSERT INTO EmailLists (
					Email,
					Country,
					Port,
					EmailType,
					ProjectID,
					EmailCategory,
					Status
				) VALUES (?, ?, ?, ?, ?, ?, ?)",
				[
					$email,
					strtoupper($country),
					strtoupper($port),
					$type,
					$project_id,
					$cat,
					$status
				]
			);

			if ($query) {
				return true;
			} else {
				return false;
			}
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function getCurrentEmail($project_id)
	{
		try {
			//code...
			return Database::rows(
				$this->db,
				"SELECT
				E.Email,
				E.Country,
				E.Port,
				ET.Description [EmailType],
				P.ProjectName [Project],
				EC.ID [EmailCategoryID],
				EC.Description [EmailCategory]
				FROM
				EmailLists E 
				LEFT JOIN EmailType ET ON ET.ID = E.EmailType
				LEFT JOIN Project P ON P.ProjectID = E.ProjectID
				LEFT JOIN EmailCategory EC ON EC.ID = E.EmailCategory
				WHERE E.ProjectID = ?
				AND E.Status = 1
				AND E.EmailType <> 4",
				[
					$project_id
				]
			);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function updateOldPort($project_id)
	{
		try {
			Database::query(
				$this->db,
				"DELETE FROM EmailLists
				WHERE EmailType <> 4
				AND EmailCategory IN (16,17)
				AND ProjectID = ?",
				[
					$project_id
				]
			);
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function getPortActive($filter)
	{
		try {
			$sql = "SELECT
			E.Email,
			E.Country,
			E.Port,
			ET.Description [EmailType],
			P.ProjectName,
			EC.Description [EmailCategory]
			FROM
			EmailLists E 
			LEFT JOIN EmailType ET ON ET.ID = E.EmailType
			LEFT JOIN Project P ON P.ProjectID = E.ProjectID
			LEFT JOIN EmailCategory EC ON EC.ID = E.EmailCategory
			WHERE P.UsePort = 1
			AND E.Status = 1
			AND E.EmailType <> 4
			AND $filter
			ORDER BY P.ProjectName ASC";
			return Database::rows(
				$this->db,
				$sql
			);
		} catch (\Exception $e) {
			return [];
		}
	}
}
