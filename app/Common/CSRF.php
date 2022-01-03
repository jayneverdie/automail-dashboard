<?php

namespace App\Common;

use \Slim\Csrf\Guard;

class CSRF
{
	public function generate() {
    $csrf = new Guard;
    $csrf->validateStorage();

    $name = $csrf->getTokenNameKey();
    $value = $csrf->getTokenValueKey();
    $pair = $csrf->generateToken();

    return [
    	'name' => $name,
    	'value' => $value,
    	'key' => $pair
    ];
  }
}