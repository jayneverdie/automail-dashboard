<?php

namespace App\Category;

class CategoryTable
{
	public function __construct() {
		$this->field = [
			'id' => 'id',
			'name' => 'category_name',
			'status' => 'category_status'
		];
		$this->table = 'web_category';
	}
}