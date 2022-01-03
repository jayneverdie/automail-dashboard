<?php

namespace App\Master;

use App\Common\Database;
use App\Common\Message;
use App\Logs\LogsAPI;

class MasterAPI
{
  public function __construct() {
		$this->db = Database::connect();
		$this->message = new Message;
    $this->logs = new LogsAPI;
	}

  public function getEmailCategory() {
		return Database::rows(
			$this->db,
      "SELECT ID,Description
      FROM EmailCategory"
		);
  }
  
  public function createEmailCategory($email_category) {

    $isExists = Database::hasRows(
      $this->db,
      "SELECT Description
      FROM EmailCategory
      WHERE Description = ?",
      [
        $email_category
      ]
    );

    if ( $isExists === true ) {
      return $this->message->result(false, 'This email category is already exists!');
    }
    
    $create = Database::query(
      $this->db,
      "INSERT INTO EmailCategory(
        Description
      ) VALUES(?)",
      [
        $email_category
      ]
    );

    if ( $create ) {
      return $this->message->result(true, 'Create successful!');
    } else {
      return $this->message->result(false, 'Create failed!');
    }
  }

  public function updateEmailCategory($name, $pk, $value, $table) {
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

}