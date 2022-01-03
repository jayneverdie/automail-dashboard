<?php

namespace App\Menu;

use App\Common\Database;
use App\Common\Message;
use App\Common\JWT;
use App\Auth\AuthAPI;
use App\Logs\LogsAPI;

class MenuAPI
{
  public function __construct() {
    $this->db = Database::connect();
    $this->message = new Message;
    $this->jwt = new JWT;
    $this->auth = new AuthAPI;
    $this->logs = new LogsAPI;
  }

  public function allPage($filter) {

    return Database::rows(
      $this->db,
      "SELECT
      M.id,
      M.menu_link,
      M.menu_name,
      M.menu_position,
      M.menu_parent,
      M.menu_order,
      S.status_name,
      M.menu_status,
      M.menu_capability,
      C2.category_name,
      C.id AS cap_id,
      C.cap_name
      FROM web_menu M
      LEFT JOIN web_capability C ON C.id = M.menu_capability
      LEFT JOIN web_status S ON S.id = M.menu_status
      LEFT JOIN web_category C2 ON C2.id = M.menu_category
      WHERE $filter
      AND C2.id = 1 -- Page
      ORDER BY id ASC"
    );
  }

  public function allApi($filter) {

    return Database::rows(
      $this->db,
      "SELECT
      M.id,
      M.menu_link,
      M.menu_name,
      M.menu_position,
      M.menu_parent,
      M.menu_order,
      S.status_name,
      M.menu_status,
      M.menu_capability,
      C2.category_name,
      C.id AS cap_id,
      C.cap_name
      FROM web_menu M
      LEFT JOIN web_capability C ON C.id = M.menu_capability
      LEFT JOIN web_status S ON S.id = M.menu_status
      LEFT JOIN web_category C2 ON C2.id = M.menu_category
      WHERE $filter
      AND C2.id = 2 -- API
      ORDER BY id ASC"
    );
  }

  public function createMenu($link, $name, $category) {

    $isExists = Database::hasRows(
      $this->db,
      "SELECT menu_link
      FROM web_menu
      WHERE menu_link = ?",
      [
        trim($link)
      ]
    );

    if ( $isExists === true ) {
      return $this->message->result(false, 'This link is already exists!');
    }

    $categoryList = [
      'page' => 1,
      'api' => 2
    ];
    
    $create = Database::query(
      $this->db,
      "INSERT INTO web_menu(
        menu_link,
        menu_name,
        menu_position,
        menu_parent,
        menu_order,
        menu_status,
        menu_category
      ) VALUES(?, ?, ?, ?, ?, ?, ?)",
      [
        $link,
        $name,
        0,
        0,
        0,
        1,
        $categoryList[$category]
      ]
    );

    if ( $create ) {
      return $this->message->result(true, 'Create successful!');
    } else {
      return $this->message->result(false, 'Create failed!');
    }
  }

  public function generateMenu(string $type, int $menu_id = 0): array {

    switch ($type) {
      case 'root':

        $rootMenu = [];

        $root = Database::rows(
          $this->db,
          "SELECT
          M.id,
          M.menu_link,
          M.menu_name,
          M.menu_position,
          M.menu_capability
          FROM web_menu M
          WHERE M.menu_status = 1
          AND M.menu_parent = 0
          AND M.menu_order <> 0
          ORDER BY M.menu_position, M.menu_order ASC"
        );

        $token = $this->jwt->verifyToken();

        if ( $token['result'] === false ) {
          return [];
        }

        foreach ($root as $row) {

          $accessMenu = $this->auth->accessLink(
            $token['data']['user_data']->role,
            $row['menu_capability']
          );

          if ($accessMenu['result'] === true || $row['menu_capability'] === 0) {
            $rootMenu[] = [
              'id' => $row['id'],
              'menu_link' => $row['menu_link'],
              'menu_name' => $row['menu_name'],
              'menu_position' => $row['menu_position']
            ];
          }
        }

        return $rootMenu;

        break;

      case 'sub':
        
        $menu = [];

        $rows =  Database::rows(
          $this->db,
          "SELECT
          M.id,
          M.menu_link,
          M.menu_name,
          M.menu_parent,
          M.menu_capability
          FROM web_menu M
          WHERE M.menu_status = 1
          AND M.menu_parent <> 0
          AND M.menu_parent = ?
          ORDER BY M.menu_order ASC",
          [
            $menu_id
          ]
        );

        $token = $this->jwt->verifyToken();

        if ( $token['result'] === false ) {
          return [];
        }

        foreach ($rows as $row) {

          $accessMenu = $this->auth->accessLink(
            $token['data']['user_data']->role,
            $row['menu_capability']
          );

          if ($accessMenu['result'] === true || $row['menu_capability'] === 0) {
            $menu[] = [
              'id' => $row['id'],
              'link' => $row['menu_link'],
              'name' => $row['menu_name'],
              'sub' => self::generateMenu('sub', $row['id'])
            ];
          }
        }

        return $menu;
        break;
      
      default:
        return [];
        break;
    }
  }

  public function deleteMenu($menu_id) {

    $delete = Database::query(
      $this->db,
      "DELETE FROM web_menu
      WHERE id = ?",
      [
        $menu_id
      ]
    );

    if ( $delete ) {
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