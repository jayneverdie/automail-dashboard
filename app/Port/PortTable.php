<?php

namespace App\Port;

class PortTable
{
	public function __construct() {

		$this->field = [
			'id' => 'ID',
			'name' => 'Email',
			'type' => 'EmailType',
			'category' => 'EmailCategory'			
		];

		$this->table = 'EmailLists';
	}
}