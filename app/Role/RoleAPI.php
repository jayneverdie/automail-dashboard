<?php

namespace App\Role;

use App\Common\Database;
use App\Common\Message;
use App\Logs\LogsAPI;

class RoleAPI
{
	public function __construct() {
    $this->db = Database::connect();
    $this->message = new Message;
    $this->logs = new LogsAPI;
	}

	public function all($filter) {
		return Database::rows(
      $this->db,
      "SELECT 
			R.id,
      R.role_name,
      R.role_default_page,
      R.role_status,
      S.status_name,
      M.menu_name
      FROM web_role R
      LEFT JOIN web_status S ON S.id = R.role_status
      LEFT JOIN web_menu M ON R.role_default_page = M.id
      WHERE $filter
      ORDER BY R.id ASC"
    );
	}
	
	public function create($name) {

    $create = Database::query(
      $this->db,
      "INSERT INTO web_role(
        role_name,
        role_status
      ) VALUES(?, ?)",
      [
        $name,
        1
      ]
    );

    if ($create) {
      return $this->message->result(true, 'Create successful!');
    } else {
      return $this->message->result(false, 'Create failed!');
    }
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
  
  public function getRoleActive() {
    return Database::rows(
      $this->db,
      "SELECT 
      id,
      role_name,
      role_status
      FROM web_role
      WHERE role_status = 1
      ORDER BY id ASC"
    );
  }

  public function getMenu() {
    return Database::rows(
      $this->db,
      "SELECT * FROM web_menu WHERE menu_status = 1"
    );
  }
}