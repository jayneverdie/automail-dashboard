<?php

namespace App\Auth;

use App\Common\Message;
use App\Auth\AuthAPI;
use App\User\UserAPI;
use App\Common\JWT;
use App\Common\Cookie;

class AuthController
{
	public function __construct() {
		$this->message = new Message;
		$this->auth = new AuthAPI;
		$this->user = new UserAPI;
		$this->jwt = new JWT;
		$this->cookie = new Cookie;
	}

	public function auth($request, $response, $args) {

		$parsedBody = $request->getParsedBody();

		if (!isset($parsedBody['login_username']) || !isset($parsedBody['login_password'])) {
	      $this->message->addFlashMessage('error', 'Username or password incorrect!');
	      return $response->withRedirect('/login', 301);
	    }

    	$authResult = $this->auth->auth($parsedBody['login_username'], $parsedBody['login_password']);

	    if ($authResult['result'] === false) {
	      $this->message->addFlashMessage('error', $authResult['message']);
	      return $response->withRedirect('/login', 301);
		}

		$userInfo = $this->user->getUserInfo($parsedBody['login_username']);

		if ( count($userInfo) === 0 ) {
			$this->message->addFlashMessage('error', 'User not found!');
			return $response->withRedirect('/login', 301);
		}

    	$token = $this->jwt->createToken([
			'username' => $userInfo[0]['user_login'],
			'fullname' => $userInfo[0]['user_firstname'] . ' ' . $userInfo[0]['user_lastname'],
			'email' => $userInfo[0]['user_email'],
	      	'role' => $userInfo[0]['user_role'],
	      	'default_page' => $userInfo[0]['menu_link'],
	      	'empid' => $userInfo[0]['EmployeeID']
	    ]);

		$this->cookie->setCookie(TOKEN_NAME, $token);
		$authResultLog = $this->auth->authResultLog($parsedBody['login_username'], $userInfo[0]['EmployeeID']);

    	return $response->withRedirect($userInfo[0]['menu_link'], 301);
	}

	public function logout($request, $response, $args) {
		$user_data = $this->jwt->verifyToken();
		
		if ( isset($_COOKIE[TOKEN_NAME]) ) {

      		unset($_COOKIE[TOKEN_NAME]);
			Cookie::setCookie(TOKEN_NAME, null, -1);
			$authResultLog = $this->auth->authResultLogout($user_data['data']['user_data']->username, $user_data['data']['user_data']->empid);

      	return $response->withRedirect('/login', 301);
    	} else {

      	return $response->withRedirect('/login', 301);
    	}
	}
}
