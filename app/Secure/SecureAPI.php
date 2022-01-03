<?php

namespace App\Secure;

use App\Common\JWT;
use App\Common\Database;
use App\Common\Message;

class SecureAPI {

	public function __construct() {
		$this->jwt = new JWT;
		$this->db = Database::connect();
		$this->message = new Message;
	}

	public function getNonce($nonce_key) {
		try {
			$nonce = hash('sha256', str_replace(' ', '', microtime() . rand(111111, 999999)));
			self::saveNonce($nonce_key, $nonce);
			return $nonce;
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

	public function saveNonce($nonce_key, $nonce) {
		if ( trim($nonce) !== '' && $nonce !== null) {
			$save = Database::query(
				$this->db,
				"INSERT INTO web_nonce(nonce_key, nonce, create_date)
				VALUES(?, ?, ?)",
				[
					$nonce_key,
					$nonce,
					date('Y-m-d H:i:s')
				]
			);

			if ($save) {
				return 'Insert success.';
			} else {
				throw new \Exception('Insert failed.');
			}
		}
	}

	public function verifyNonce($nonce_key, $nonce) {
		try {
			$verify = Database::hasRows(
				$this->db,
				"SELECT id FROM web_nonce
				WHERE nonce_key = ?
				AND nonce = ?
				AND used = 0",
				[
					$nonce_key,
					$nonce
				]
			);

			if ($verify === true) {
				$updateNonce = Database::query(
					$this->db,
					"UPDATE web_nonce 
					SET used = 1,
					update_date = ?
					WHERE nonce_key = ?
					AND nonce = ?",
					[
						date('Y-m-d H:i:s'),
						$nonce_key,
						$nonce
					]
				);

				if (!$updateNonce) {
					throw new \Exception('Update nonce failed.');
				}
			}

			return $verify;
		} catch (\Exception $e) {
			throw new \Exception('Verified failed.');
		}
	}
}