<?php

namespace App\Auth;

use App\Common\JWT;
use App\Common\Database;
use App\Common\Message;

class AuthAPI
{
  public function __construct() {
    $this->db = Database::connect();
    $this->message = new Message;
  }

  public function accessLink($role_id, $cap_id = null) {

    if ($cap_id === null) {
      $currentUrl = APP_ROOT . $_SERVER['REQUEST_URI'];

      if ($currentUrl === '') $currentUrl = '/';

      $currentUrl = explode('?', $currentUrl)[0];

      $getCapByMenuLink = Database::rows(
        $this->db,
        "SELECT TOP 1 menu_capability
        FROM web_menu
        WHERE menu_link = ?",
        [
          str_replace('//', '/', trim($currentUrl))
        ]
      );

      if ( count($getCapByMenuLink) === 0 ) {
        return $this->message->result(false, 'Not passed!');
      }

      $cap_id = $getCapByMenuLink[0]['menu_capability'];
    }

    $checkCap = self::checkCapabilityByRole(
      $role_id,
      $cap_id
    );

    if ($checkCap === true) {
      return $this->message->result(true, 'Passed!');


    } else {
      return $this->message->result(false, 'Not passed!');
    }
  }

  public function checkCapabilityByRole($role_id, $cap_slug) {
    return Database::hasRows(
      $this->db,
      "SELECT
      P.cap_id
      FROM web_permission P
      LEFT JOIN web_capability C
      ON P.cap_id = C.id
      WHERE C.id = ?
      AND P.role_id = ?",
      [
        $cap_slug,
        $role_id
      ]
    );
  }

  public function auth($username, $password) {
    if ( self::isUserExists($username) === false ) {
      return $this->message->result(false, 'User not found!');
    }

    $passwordHashed = Database::rows(
      $this->db,
      "SELECT user_pass
      FROM web_user
      WHERE user_login = ?",
      [
        htmlspecialchars($username)
      ]
    );

    if ( count($passwordHashed) === 0 ) {
      return $this->message->result(false, 'Password incorrect!');
    }

    if ( password_verify($password, $passwordHashed[0]['user_pass']) === false ) {
      return $this->message->result(false, 'Password incorrect!');
    } else {
      return $this->message->result(true, 'Passed!');
    }
  }

  public function isUserExists($username) {
    return Database::hasRows(
      $this->db,
      "SELECT user_login
      FROM web_user
      WHERE user_login = ?",
      [
        htmlspecialchars($username)
      ]
    );
  }

  public function authResultLog($username, $empid) {

    $UpdateLastlogin = Database::query(
      $this->db,
      "UPDATE web_user SET LastestLogin = ? WHERE user_login = ? ",
      [
        date('Y-m-d H:i:s'),
        $username
      ]
    );

    $Savelog = Database::query(
      $this->db,
      "INSERT INTO LogsLogin (username,LoginDate)
      VALUES (?,?)",
      [
        $username,
        date('Y-m-d H:i:s')
      ]
    );

    $InsertlogApp = Database::query(
      $this->db,
      "INSERT INTO [EA_APP].[dbo].[TB_LOG_APP] (EMP_CODE,USER_NAME,HOST_NAME,LOGIN_DATE,PROJECT_NAME)
      VALUES (?,?,?,?,?)",
      [
        $empid,
        $username,
        gethostbyaddr($_SERVER['REMOTE_ADDR']),
        date('Y-m-d H:i:s'),
        'Auto Mail(Export)'
      ]
    );

  }

  public function authResultLogout($username, $empid) {

    $InsertlogApp = Database::query(
      $this->db,
      "INSERT INTO [EA_APP].[dbo].[TB_LOG_APP] (EMP_CODE,USER_NAME,HOST_NAME,LOGOUT_DATE,PROJECT_NAME)
      VALUES (?,?,?,?,?)",
      [
        $empid,
        $username,
        gethostbyaddr($_SERVER['REMOTE_ADDR']),
        date('Y-m-d H:i:s'),
        'Auto Mail(Export)'
      ]
    );

  }

}
