<?php

namespace App\Project;

class ProjectTable
{
	public function __construct() {
		$this->field = [
			'id' => 'ProjectID',
			'project_name' => 'ProjectName',
			'use_port' => 'UsePort'
		];
		$this->table = 'Project';
	}
}