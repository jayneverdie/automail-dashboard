<?php

namespace App\User;

use App\Common\Database;
use App\Common\Message;
use App\Common\JWT;
use App\Logs\LogsAPI;

class UserAPI
{
	public function __construct() {
    $this->db = Database::connect();
    $this->message = new Message;
    $this->jwt = new JWT;
    $this->logs = new LogsAPI;
	}

	public function getUserInfo($username) {
    return Database::rows(
      $this->db,
      "SELECT
        U.user_login,
        U.user_email,
        U.user_status,
        U.user_firstname,
        U.user_lastname,
        U.user_registered,
        U.user_role,
        R.role_default_page,
        M.menu_name,
        M.menu_link,
        U.EmployeeID
        FROM web_user U
        LEFT JOIN web_role R ON U.user_role = R.id
        LEFT JOIN web_menu M ON R.role_default_page = M.id
        WHERE U.user_login = ?
        AND U.user_status = 1",
        [
          $username
        ]
    );
  }

  public function updateProfile($user_login, $user_data) {
    $update = Database::query(
      $this->db,
      "UPDATE web_user
      SET user_email = ?,
      user_firstname = ?,
      user_lastname = ?
      WHERE user_login = ?",
      [
        $user_data['email'],
        $user_data['firstname'],
        $user_data['lastname'],
        $user_login
      ]
    );

    if ($update) {
      return $this->message->result(true, '');
    } else {
      return $this->message->result(false, '');
    }
  }

  public function changePassword($username, $password) {

    if ( trim($password) === '' ) {
      return $this->message->result(false, 'Password must not null!');
    }

    $passwordSalt = password_hash($password, PASSWORD_DEFAULT);

    $update = Database::query(
      $this->db,
      "UPDATE web_user
      SET user_pass = ?
      WHERE user_login = ?",
      [
        $passwordSalt,
        $username
      ]
    );
    if ($update) {
      return $this->message->result(true, 'Update success!');
    } else {
      return $this->message->result(false, 'Update failed!');
    }
  }

  public function all($filter) {
    return Database::rows(
      $this->db,
      "SELECT
      W.id,
      W.user_login,
      W.user_email,
      W.user_registered,
      W.user_firstname,
      W.user_lastname,
      R.id AS role_id,
      R.role_name AS user_role,
      W.user_status,
      S.status_name
      FROM web_user W
      LEFT JOIN web_role R ON R.id = W.user_role
      LEFT JOIN web_status S ON S.id = W.user_status
      WHERE $filter
      ORDER BY W.user_status , w.user_login asc"
    );
  }
	public function AllLogin() {
    return Database::rows(
      $this->db,
      "SELECT
			 WL.user_login,
			 HR.EMPNAME,
			 HR.EMPLASTNAME,
			 WL.LastestLogin,
			 HR.COMPANYNAME,
			 HR.DIVISIONNAME,
			 HR.POSITIONNAME,
			 HR.DEPARTMENTNAME,
			  HR.CODEMPID
			 FROM web_user WL
			 JOIN [HRTRAINING].[dbo].[EMPLOYEE] HR
			 ON HR.CODEMPID = WL.EmployeeID
			 WHERE WL.LastestLogin IS NOT NULL ORDER BY WL.LastestLogin DESC"
    );
  }

	public function CountUser() {
    return Database::rows(
      $this->db,
      " SELECT
			COUNT(id) as TotalUser
			FROM web_user"
    );
  }

  public function createUser($user_login, $user_password,$user_Employee,$user_firstnameAdd,$user_lastnameAdd,$user_email) {

    $pass_salt = password_hash($user_password, PASSWORD_DEFAULT);

    $checkUser = sqlsrv_has_rows(sqlsrv_query(
      $this->db,
      "SELECT user_login
      FROM web_user
      WHERE user_login = ?",
      [
        $user_login
      ]
    ));

    if ( $checkUser === true ) {
      return [
        'result' => false,
        'message' => "This " . $user_login . " has already exists."
      ];
    }

    $createUser = sqlsrv_query(
      $this->db,
      "INSERT INTO web_user(
        user_login,
        user_pass,
        user_registered,
        user_status,
        EmployeeID,
        user_firstname,
        user_lastname,
        user_email
      ) VALUES(?, ?, ?, ?, ?, ?, ?, ?)",
      [
        strtolower($user_login),
        $pass_salt,
        date('Y-m-d H:i:s'),
        2,
        strtolower($user_Employee),
        strtolower($user_firstnameAdd),
        strtolower($user_lastnameAdd),
        strtolower($user_email)
      ]
    );

    if ( $createUser ) {
      // LogEA 
      $this->logs->InsertLogUser(strtolower($user_Employee),strtolower($user_login),gethostname(),'Auto Mail(Export)');

      return $this->message->result(true, 'Create successful!');
    } else {
      return $this->message->result(false, 'Create failed!');
    }
  }

  public function update($name, $pk, $value) {
    try {
      $update = Database::query(
        $this->db,
        "UPDATE web_user
        SET $name = ?
        WHERE id = ?",
        [
          $value,
          $pk
        ]
      );

      if ( !$update ) {
        throw new \Exception('Error: update failed.');
      }
      // LogEA 
      if ($name="user_status") {
        $userInfo = $this->logs->getUserInfo($pk);
        if ($value==1) {
          $this->logs->InsertLogUser((int)$userInfo[0]['EmployeeID'],$userInfo[0]['user_login'],gethostname(),'Auto Mail(Export)');
        }else{
          $this->logs->DeleteLogUser((int)$userInfo[0]['EmployeeID'],$userInfo[0]['user_login'],gethostname(),'Auto Mail(Export)');
        }
      }      
      return 'Update success';
    } catch (\Exception $e) {
      throw new \Exception('Error: update failed.');
    }
  }

  public function resetPassword($user_id, $password) {
    $passwordHashed = password_hash($password, PASSWORD_DEFAULT);

    $update = Database::query(
      $this->db,
      "UPDATE web_user
      SET user_pass = ?
      WHERE id = ?",
      [
        $passwordHashed,
        $user_id
      ]
    );

    if ($update) {
      return $this->message->result(true, 'Update successful!');
    } else {
      return $this->message->result(false, 'Update failed!');
    }
  }

  public function userCan($cap_slug) {

    $user = $this->jwt->verifyToken();

    if ( $user['result'] === false ) {
      return false;
    }

    return Database::hasRows(
      $this->db,
      "SELECT P.id
      FROM web_permission P
      LEFT JOIN web_capability C
      ON C.id = P.cap_id
      WHERE C.cap_slug = ?
      AND P.role_id = ?",
      [
        $cap_slug,
        $user['data']['user_data']->role
      ]
    );
  }

	public function getEmployee($filter) {
    return Database::rows(
      $this->db,
      "SELECT TOP 50
       EP.CODEMPID,
       EP.EMPNAME,
       EP.EMPLASTNAME,
       TM.EMAIL
       FROM [HRTRAINING].[dbo].[EMPLOYEE] EP 
       LEFT JOIN [HRTRAINING].[dbo].[TEMPLOY1] TM ON EP.CODEMPID = TM.CODEMPID
       WHERE EP.STATUS in (1,3) AND $filter "
    );
  }

  public function saveNewPassword($email, $password) {
    try {
      $passwordSalt = password_hash($password, PASSWORD_DEFAULT);

      $update = Database::query(
        $this->db,
        "UPDATE web_user
        SET user_pass = ?
        WHERE user_email = ?",
        [
          $passwordSalt,
          $email
        ]
      );

      if ($update) {
        return 'Update new password success.';
      } else {
        throw new \Exception('Update new password failed.');
      }
    } catch (\Exception $e) {
      throw new \Exception('Update new password failed.');
    }
  }

  public function registerUser($empid, $username, $password, $email, $firstname, $lastname) {

    try {
      $passwordSalt = password_hash($password, PASSWORD_DEFAULT);

      $save = Database::query(
        $this->db,
        "INSERT INTO web_user(
          user_login,
          user_pass,
          user_email,
          user_firstname,
          user_lastname,
          user_registered,
          user_status,
          EmployeeID
        ) VALUES (
          ?, ?, ?,
          ?, ?, ?
        )",
        [
          $username,
          $passwordSalt,
          $email,
          $firstname,
          $lastname,
          date('Y-m-d H:i:s'),
          0,
          $empid
        ]
      );

      if (!$save) {
        throw new \Exception('Register failed.');
      }

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

  public function chkEmail($email) {
    try {
      return Database::hasRows(
        $this->db,
        "SELECT TOP 1 *
        FROM web_user
        WHERE user_email = ?",
        [
          $email
        ]
      );
    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }
  }

}
