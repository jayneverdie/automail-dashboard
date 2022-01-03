<?php

namespace App\Email;

class EmailListsTable
{
	public function __construct() {
		$this->field = [
			'id' => 'ID',
			'customer_code' => 'CustomerCode',
			'email' => 'Email',
		    'port' => 'Port',
		    'project_id' => 'ProjectID',
		    'email_type' => 'EmailType',
		    'email_category' => 'EmailCategory',
		    'status' => 'Status',
		    'empcode_ax' => 'EmpCode_AX'
		];
		$this->table = 'EmailLists';
	}
}