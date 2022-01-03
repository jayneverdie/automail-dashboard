<?php

namespace App\User;

class UserTable
{
	public function __construct() {
		$this->field =  [
			'id' => 'id',
                  'login' => 'user_login',
                  'password' => 'user_pass',
                  'email' => 'user_email',
                  'status' => 'user_status',
                  'firstname' => 'user_firstname',
                  'lastname' => 'user_lastname',
                  'role' => 'user_role',
									'CODEMPID' => 'CODEMPID',
                  'EMPNAME' => 'EMPNAME',
									'EMPLASTNAME' => 'EMPLASTNAME'

		];
            $this->table = 'web_user';
	}
}
