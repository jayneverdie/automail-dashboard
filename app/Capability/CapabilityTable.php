<?php

namespace App\Capability;

class CapabilityTable
{
	public function __construct() {
		$this->field = [
			'id' => 'id',
			'slug' => 'cap_slug',
			'name' => 'cap_name',
			'category' => 'cap_category',
			'status' => 'cap_status'		
		];
		$this->table = 'web_capability';
	}
}