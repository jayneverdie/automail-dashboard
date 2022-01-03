<?php

namespace App\Email;

class EmailMappingTable 
{
	public function __construct() {
		$this->field = [
			'id' => 'ID',
			'customer_code' => 'CustomerCode',
			'email' => 'Email'
		];
		$this->table = 'EmailMapping';
	}
}
