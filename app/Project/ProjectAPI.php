<?php

namespace App\Project;

use App\Common\Database;
use App\Common\Message;
use App\Common\JWT;
use App\Auth\AuthAPI;

class ProjectAPI
{
  public function __construct() {
    $this->db = Database::connect();
    $this->message = new Message;
    $this->jwt = new JWT;
    $this->auth = new AuthAPI;
  }

  public function getEmailLists() {
    return Database::rows(
      $this->db,
      "SELECT
      P.ProjectID,
      P.ProjectName,
      P.UsePort,
      S.status_name
      FROM Project P
      LEFT JOIN web_status S ON P.UsePort=S.id"
    );
  }

  public function createProject($user_p) {

    $createUser = sqlsrv_query(
      $this->db,
      "INSERT INTO Project(
        ProjectName
      ) VALUES(?)",
      [
        $user_p
      ]
    );
      if($createUser)
      {
          return $this->message->result(true, 'successful');
      }
      else {

          return $this->message->result(true, 'Create NO!');

        }


  }

  public function deleteProject($id) {
		$create = Database::query(
	      $this->db,
	      "DELETE FROM Project
	      WHERE ProjectID=?",
	      [
	        $id
	      ]
	    );

	    if ($create) {
	      return $this->message->result(true, 'Delete successful!');
	    } else {
	      return $this->message->result(false, 'Delete failed!');
	    }
	}

  public function update($name, $pk, $value, $table) {
    $update = Database::query(
      $this->db,
      "UPDATE $table
      SET $name = ?
      WHERE ProjectID = ?",
      [
        $value,
        $pk
      ]
    );

    if ( $update ) {
        return $this->message->result(true, 'Update successful!');
    } else {
        return $this->message->result(false, 'Update failed!');
    }
}


}
