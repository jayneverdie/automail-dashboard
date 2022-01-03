<?php

namespace App\Capability;

use App\Common\Database;
use App\Common\Message;
use App\Logs\LogsAPI;

class CapabilityAPI
{

	public function __construct() {
		$this->db = Database::connect();
		$this->message = new Message;
    $this->logs = new LogsAPI;
	}

	public function getActive() {
		$result = Database::rows(
			$this->db,
			"SELECT
			id,
			cap_slug,
			cap_name,
			cap_status
			FROM web_capability
			WHERE cap_status = 1"
		);

		return $result;
	}

	public function getCapabilityByRole($role_id, $category_id) {
		return Database::rows(
      $this->db,
      "SELECT 
      C.id,
      C.cap_name,
      CASE 
        WHEN P.cap_id IS NULL OR P.cap_id = '' THEN 0
        ELSE 1
      END AS selected
      FROM web_capability C
      LEFT JOIN web_permission P
      ON P.cap_id = C.id
      AND P.role_id = ?
      WHERE C.cap_category = ?
      ORDER BY C.cap_name ASC",
      [
        $role_id,
        $category_id
      ]
    );
  }
  
  public function getCapabilityByCategory($category_id) {
    return Database::rows(
      $this->db,
      "SELECT 
      C.id,
      C.cap_name
      FROM web_capability C
      WHERE C.cap_category = ?",
      [
        $category_id
      ]
    );
  }

	public function all($filter) {
		return Database::rows(
			$this->db,
			"SELECT 
      C.id,
      C.cap_slug,
      C.cap_name,
      CC.category_name,
      S.status_name
      FROM web_capability C
      LEFT JOIN web_status S ON S.id = C.cap_status
      LEFT JOIN web_category CC ON CC.id = C.cap_category
      WHERE $filter"
		);
	}

	public function updateCapability($role_id, $cap_id) {

		if ( \sqlsrv_begin_transaction($this->db) === false ) {
			return $this->message->result(false, 'transaction failed!');
		}

		$delete = Database::query(
      $this->db,
      "DELETE FROM web_permission
      WHERE role_id = ?",
      [
        $role_id
      ]
		);
		
		if ( !$delete ) {
			sqlsrv_rollback($this->db);
			return $this->message->result(false, 'compact permission failed!');
		}

		foreach ($cap_id as $cap) {

      $isCapIsAlreadyExists = Database::hasRows(
        $this->db,
        "SELECT id
        FROM web_permission
        WHERE role_id = ?
        AND cap_id = ?",
        [
          $role_id,
          $cap['value']
        ]
      );

      if ( $isCapIsAlreadyExists === false) {

        $insert = Database::query(
          $this->db,
          "INSERT INTO web_permission(
            role_id,
            cap_id
          ) VALUES(?, ?) ",
          [
            $role_id,
            $cap['value']
          ]
        );

        if ( !$insert ) {
          \sqlsrv_rollback($this->db);
          return $this->message->result(false, 'insert new failed!');
        }
      }
    }

    \sqlsrv_commit($this->db);
    return $this->message->result(true, 'Update successful!');
  }
  
  public function update($name, $pk, $value, $table) {
    $update = Database::query(
      $this->db,
      "UPDATE $table
      SET $name = ?
      WHERE id = ?",
      [
        $value,
        $pk
      ]
    );

    if ( $update ) {
      $this->logs->InsertLogs($name,$pk,$value,$table);
      return $this->message->result(true, 'Update successful!');
    } else {
      return $this->message->result(false, 'Update failed!');
    }
  }

  public function create($slug, $name) {

    $isExists = Database::hasRows(
      $this->db,
      "SELECT cap_slug 
      FROM web_capability
      WHERE cap_slug = ?",
      [
        trim($slug)
      ]
    );

    if ( $isExists === true ) {
      return $this->message->result(false, 'This slug already exists!');
    }

    $create = Database::query(
      $this->db,
      "INSERT INTO web_capability(cap_slug, cap_name, cap_status)
      VALUES(?, ?, ?)",
      [
        $slug,
        $name,
        1
      ]
    );

    if ( $create ) {
      return $this->message->result(true, 'Create successful!');
    } else {
      return $this->message->result(false, 'Create failed!');
    }
  }

  public function delete($id) {

    $isUsing = Database::hasRows(
      $this->db,
      "SELECT cap_id 
      FROM web_permission
      WHERE cap_id = ?",
      [
        $id
      ]
    );

    if ( $isUsing === true ) {
      return $this->message->result(false, 'This capability is using!');
    }

    $delete = Database::query(
      $this->db,
      "DELETE FROM web_capability
      WHERE id = ?",
      [
        $id
      ]
    );

    if ( $delete ) {
      return $this->message->result(true, 'Delete successful!');
    } else {
      return $this->message->result(false, 'Delete failed!');
    }
  }
}