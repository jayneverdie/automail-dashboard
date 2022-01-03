<?php

namespace App\Menu;

class MenuTable
{
	public function __construct() {
		$this->field =  [
			'id' => 'id',
                  'link' => 'menu_link',
                  'name' => 'menu_name',
                  'position' => 'menu_position',
                  'parent' => 'menu_parent',
                  'order' => 'menu_order',
                  'capability' => 'menu_capability',
                  'category' => 'menu_category',
                  'status' => 'menu_status'
		];
            $this->table = 'web_menu';
	}
}