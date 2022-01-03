<?php

namespace App\Common;

use Webmozart\Assert\Assert;

class Validate {

	public function isEmail($email) {
		Assert::notEmpty($email);
		Assert::notNull($email);
		$isEmail = \filter_var(trim($email), FILTER_VALIDATE_EMAIL);
		if ( false === $isEmail ) {
			throw new \Exception($email . ' is not email.');
		}
		return $isEmail;
	}
}