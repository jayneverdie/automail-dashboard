<?php

namespace App\Role;

class RoleTable
{
	public function __construct() {
		$this->field =  [
			'id' => 'id',
      		'name' => 'role_name',
      		'status' => 'role_status',
      		'menupage' => 'role_default_page'
		];

		$this->table = 'web_role';
	}
}