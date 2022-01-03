<?php

namespace App\Common;

use \Firebase\JWT\JWT as FirebaseJWT;
use App\Common\Message;

class JWT 
{
	public function __construct() {
		$this->message = new Message;
	}

	public function createToken(array $payload = []) {

		$data = [
			'typ'=> 'JWT',
			'nbf' => time(),
			'exp' => time() + (24*60*60),
			'user_data' => $payload
		];

		return FirebaseJWT::encode($data, APP_KEY);
	}

	public function verifyToken() {

		if ( !isset($_COOKIE[TOKEN_NAME]) )  {
			return $this->message->result(false, 'token invalid!');
		}

		try {
			$payload = (array)FirebaseJWT::decode($_COOKIE[TOKEN_NAME], APP_KEY, array('HS256'));
			return $this->message->result(true, 'token valid!', $payload);
		} catch (\Exception $e) {
			return $this->message->result(false, 'token invalid!');
		}
	}
}
