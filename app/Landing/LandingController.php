<?php

namespace App\Landing;

use App\Common\View;
use App\Common\CSRF;
use App\Secure\SecureAPI;
use App\User\UserAPI;
use App\Common\JWT;

class LandingController
{
  public function __construct() {
    $this->csrf = new CSRF;
    $this->view = new View;
    $this->secure = new SecureAPI;
    $this->user = new UserAPI;
    $this->jwt = new JWT;
  }

  public function login($request, $response, $args) {

    $csrf = $this->csrf->generate();

    return $this->view->render('pages/login', [
      'name' => $csrf['name'],
			'value' => $csrf['value'],
			'key' => $csrf['key']
    ]);
  }

  public function home($request, $response, $args) {
     $user_data = $this->jwt->verifyToken();
     $users =  $this->user->getUserInfo($user_data['data']['user_data']->username);
     $link = $users[0]['menu_link'];

     if ($link === "/")
     {
        return $this->view->render('pages/home');
     }

     return $response->withRedirect($link, 301);

    
  }

  public function unauthorize() {
    return $this->view->render('pages/unauthorize');
  }

  public function notfound() {
    return $this->view->render('pages/notfound');
  }

  public function forgotPassword($request, $response, $args) {

    $csrf = $this->csrf->generate();

    return $this->view->render('pages/user/forgot_password', [
      'name' => $csrf['name'],
			'value' => $csrf['value'],
			'key' => $csrf['key']
    ]);
  }

  public function newPassword($request, $response, $args) {
    $csrf = $this->csrf->generate();

    $filtered_email = \filter_var(trim($args['email']), FILTER_VALIDATE_EMAIL);

    if ( false === $filtered_email) {
      throw new \Exception('Email incorrect!');
    }

    try {
      $verifyNonce = $this->secure->verifyNonce($filtered_email, $args['nonce']);

      if ($verifyNonce === false) {
        return $response->withRedirect('/unauthorize', 301);
      }

    } catch (\Exception $e) {
      throw new \Exception($e->getMessage());
    }

    return $this->view->render('pages/user/new_password', [
      'name' => $csrf['name'],
			'value' => $csrf['value'],
      'key' => $csrf['key'],
      'email' => $filtered_email
    ]);
  }

  public function register($request, $response, $args) {
    $csrf = $this->csrf->generate();

    return $this->view->render('pages/user/register', [
      'name' => $csrf['name'],
			'value' => $csrf['value'],
			'key' => $csrf['key']
    ]);
  }
}
