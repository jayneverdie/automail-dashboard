<?php

namespace App\Menu;

use App\Menu\MenuAPI;
use App\Menu\MenuTable;
use App\Common\View;
use App\Common\Datatables;

class MenuController 
{
  private $menu = null;

  public function __construct() {
    $this->menu = new MenuAPI;
    $this->view = new View;
    $this->menu_table = new MenuTable;
    $this->datatables = new Datatables;
  }

  public function page($request, $response, $args) {
    return $this->view->render('pages/menu_page');
  }

  public function api($request, $response, $args) {
    return $this->view->render('pages/menu_api');
  }

  public function allPage($request, $response, $args) { 
    $parsedBody = $request->getParsedBody();
    
    $data = $this->menu->allPage($this->datatables->filter($parsedBody));
    $pack = $this->datatables->get($data, $parsedBody);

    return $response->withJson($pack);
  }

  public function allApi($request, $response, $args) { 
    $parsedBody = $request->getParsedBody();
    
    $data = $this->menu->allApi($this->datatables->filter($parsedBody));
    $pack = $this->datatables->get($data, $parsedBody);

    return $response->withJson($pack);
  }

  public function createMenu($request, $response, $args) {
    $parsedBody = $request->getParsedBody();

    $create = $this->menu->createMenu(
      htmlspecialchars($parsedBody['menu_link']),
      htmlspecialchars($parsedBody['menu_name']),
      htmlspecialchars($parsedBody['menu_category'])
    );

    return $response->withJson($create);
  }

  public function generateMenuHTML($head = "") {

    $roots = $this->menu->generateMenu('root');

    $menu = [];

    foreach ($roots as $v) {
      $menu[] = [
        'id' => $v['id'],
        'link' => $v['menu_link'],
        'name' => $v['menu_name'],
        'sub' => $this->menu->generateMenu('sub', $v['id'])
      ];
    }

    $menu_generated = '';

    $menu_generated .= '<ul class="sidebar-menu" data-widget="tree">';

    if ($head !== "") {
      $menu_generated .= '<li class="header">' . htmlspecialchars($head) . '</li>';
    }
    
    foreach ($menu as $v) {
      if ( count($v['sub']) === 0 ) {
        $menu_generated .= '
          <li>
            <a href="' . $v['link'] . '">
              <i class="fa fa-link"></i> 
              <span>' . $v['name'] . '</span>
            </a>
          </li>';
      } else {
        $menu_generated .= '
          <li class="treeview">
            <a href="#">
              <i class="fa fa-link"></i> 
              <span> ' . $v['name'] . ' </span>
							<span class="pull-right-container">
								<i class="fa fa-angle-left pull-right"></i>
							</span>
            </a>';
        $menu_generated .= '<ul class="treeview-menu">';
        foreach ($v['sub'] as $v2) {
          if ( count($v2['sub']) === 0 ) {
            $menu_generated .= '
              <li>
                <a href="' . $v2['link'] . '">
                  <span>' . $v2['name'] . '</span>
                </a>
              </li>';
          } else {
            $menu_generated .= '
              <li class="treeview">
                <a href="#">
                  <span> ' . $v2['name'] . ' </span>
                  <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                  </span>
                </a>';
            $menu_generated .= '<ul class="treeview-menu">';
            foreach ($v2['sub'] as $v3) {
              $menu_generated .= '
                <li>
                  <a href="' . $v3['link'] . '">
                    <span>' . $v3['name'] . '</span>
                  </a>
                </li>';
            }
            $menu_generated .= '</ul></li>';
          }
        }
        $menu_generated .= '</ul></li>';
      }
    }
    $menu_generated .= '</ul>';

    return $menu_generated;
  }

  public function deleteMenu($request, $response, $args) {
    
    $parsedBody = $request->getParsedBody();

    $delete = $this->menu->deleteMenu(
      $parsedBody['id']
    );

    return $response->withJson($delete);
  }

  public function update($request, $response, $args) {
    $parsedBody = $request->getParsedBody();

    $result = $this->menu->update(
      $this->menu_table->field[$parsedBody['name']],
      $parsedBody['pk'], 
      $parsedBody['value'],
      $this->menu_table->table
    );

    return $response->withJson($result);
  }
}