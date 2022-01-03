<?php

use App\Auth\AuthAPI;
use App\Common\JWT;
use App\Common\Message;

$authApi = new AuthAPI;
$jwt = new JWT;
$message = new Message;

$app->add(new \Zeuxisoo\Whoops\Provider\Slim\WhoopsMiddleware);

// auth login
$auth = function ($request, $response, $next) use ($jwt, $message) {

	$token = $jwt->verifyToken();

	if ( $token['result'] === false ) {
		$message->addFlashMessage('error', 'Please login!');
		return $response->withRedirect('/login', 301);
	}

	return $next($request, $response);
};

// access page 
$accessPage = function($request, $response, $next) use ($jwt, $message, $authApi) {

	$token = $jwt->verifyToken();

	if ( $token['result'] === false ) {
		$message->addFlashMessage('error', 'Please login!');
		return $response->withRedirect('/login', 301);
	}

	$userCanAccess = $authApi->accessLink($token['data']['user_data']->role);

	if ( $userCanAccess['result'] === false ) {
		return $response->withRedirect('/unauthorize', 301);
	}

	return $next($request, $response);
};

// access api 
$accessApi = function($request, $response, $next) use ($jwt, $message, $authApi) {

	$token = $jwt->verifyToken();

	if ( $token['result'] === false ) {
		$message->addFlashMessage('error', 'Please login!');
		return $response->withRedirect('/login', 301);
	}

	$userCanAccess = $authApi->accessLink($token['data']['user_data']->role);

	if ( $userCanAccess['result'] === false ) {
		return $response->withJson($message->result(false, 'Your are not unauthorized to access this section!'));
	}

	return $next($request, $response);
};