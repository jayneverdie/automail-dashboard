<?php

namespace App\Master;

class MasterTable
{
  public function __construct() {
    $this->field =  [
      'id'          => 'ID',
      'description' => 'Description'
    ];
    $this->table = 'EmailCategory';
  }
}