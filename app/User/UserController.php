<?php

namespace App\User;

use App\User\UserAPI;
use App\Auth\AuthAPI;
use App\Common\CSRF;
use App\Common\View;
use App\Common\JWT;
use App\Common\Message;
use App\User\UserTable;
use App\Common\Datatables;
use App\Email\EmailAPI;
use App\Secure\SecureAPI;
use Webmozart\Assert\Assert;
use App\Common\Validate;

class UserController
{
  public function __construct() {
    $this->user = new UserAPI;
    $this->auth = new AuthAPI;
    $this->csrf = new CSRF;
    $this->view = new View;
    $this->jwt = new JWT;
    $this->message = new Message;
    $this->user_table = new UserTable;
    $this->datatables = new Datatables;
    $this->email = new EmailAPI;
    $this->secure = new SecureAPI;
    $this->validate = new Validate;
  }

  public function index($request, $response, $args) {
    return $this->view->render('pages/user/user');
  }

  public function saveChangePassword($request, $response, $args) {

    $parsedBody = $request->getParsedBody();

    if (strlen($parsedBody['new_pass']) < 8) {
      $this->message->addFlashMessage('error', 'Password must more than 8 character!');
      return $response->withRedirect('/user/change_password', 301);
    }

    if (trim($parsedBody['new_pass']) !== trim($parsedBody['confirm_new_pass'])) {
      $this->message->addFlashMessage('error', 'Password incorrects!');
      return $response->withRedirect('/user/change_password', 301);
    }

    $user_data = $this->jwt->verifyToken();

    $checkOldPass = $this->auth->auth(
      $user_data['data']['user_data']->username,
      $parsedBody['old_pass']
    );

    if ( $checkOldPass['result'] === false ) {
      $this->message->addFlashMessage('error', 'Old password incorrect!');
      return $response->withRedirect('/user/change_password', 301);
    }

    if ($parsedBody['new_pass'] !== $parsedBody['confirm_new_pass']) {
      $this->message->addFlashMessage('error', ' Password not match!');
      return $response->withRedirect('/user/change_password', 301);
    }

    $updatePassword = $this->user->changePassword(
      $user_data['data']['user_data']->username,
      $parsedBody['new_pass']
    );

    if ($updatePassword['result'] === true) {
      $this->message->addFlashMessage('success', 'Change password successful!');
      return $response->withRedirect('/user/change_password', 301);
    } else {
      $this->message->addFlashMessage('error', 'Change password failed!');
      return $response->withRedirect('/user/change_password', 301);
    }
  }

  public function updateProfile($request, $response, $args) {

    $parsedBody = $request->getParsedBody();
    
    $updateData = [
      'email' => htmlspecialchars($parsedBody['user_email']),
      'firstname' => htmlspecialchars($parsedBody['user_firstname']),
      'lastname' => htmlspecialchars($parsedBody['user_lastname'])
    ];

    if ($_FILES["InputFileUpload"]["name"]) {
      
      $typename = strrchr($_FILES['InputFileUpload']['name'],".");

      // delete old file
      $files = glob('../public/assets/images/'.$parsedBody['user_login'].'/*');
      foreach($files as $file){
        if(is_file($file))
          unlink($file);
      }
      // create new file
      move_uploaded_file($_FILES["InputFileUpload"]["tmp_name"],iconv('UTF-8','windows-874',"../public/assets/images/".$parsedBody['user_login']."/".$parsedBody['user_login'].$typename));
    }

    $update = $this->user->updateProfile(
      $parsedBody['user_login'],
      $updateData
    );

    if ($update['result'] === true) {
      $this->message->addFlashMessage('success', 'Update successful!');
    } else {
      $this->message->addFlashMessage('error', 'Update failed!');
    }

    return $response->withRedirect('/user/profile', 301);
  }

  public function all($request, $response, $args) {

    $parsedBody = $request->getParsedBody();

    $data = $this->user->all($this->datatables->filter($parsedBody));
    $pack = $this->datatables->get($data, $parsedBody);

    return $response->withJson($pack);
  }
  public function AllLogin($request, $response, $args) {
    return $response->withJson(["data" => $this->user->AllLogin()]);
  }
  public function CountUser($request, $response, $args) {
    return $response->withJson(["data" => $this->user->CountUser()]);
  }

  public function createUser($request, $response, $args) {

    $parsedBody = $request->getParsedBody();

    if (!file_exists('../public/assets/images/' . $parsedBody['user_login'])) {
      mkdir('../public/assets/images/' .$parsedBody['user_login'], 0777, true);
    }

    $create = $this->user->createUser(
      htmlspecialchars($parsedBody['user_login']),
      htmlspecialchars($parsedBody['user_password']),
      htmlspecialchars($parsedBody['user_Employee']),
      htmlspecialchars($parsedBody['user_firstnameAdd']),
      htmlspecialchars($parsedBody['user_lastnameAdd']),
      htmlspecialchars($parsedBody['user_email'])
    );

    return $response->withJson($create);
  }

  public function update($request, $response, $args) {
    try {
      $parsedBody = $request->getParsedBody();

      $result = $this->user->update(
        $this->user_table->field[$parsedBody['name']],
        $parsedBody['pk'],
        $parsedBody['value']
      );

      return $response->withJson($this->message->result(true, $result));
    } catch (\Exception $e) {
      return $response->withJson($this->message->result(false, $e->getMessage()));
    }
  }

  public function profile($request, $response, $args) {

    $csrf = $this->csrf->generate();
    $user_data = $this->jwt->verifyToken();

    return $this->view->render('/pages/user/profile', [
      'name' => $csrf['name'],
			'value' => $csrf['value'],
      'key' => $csrf['key'],
      'user_data' => $this->user->getUserInfo($user_data['data']['user_data']->username)
    ]);
  }

  public function changePassword($request, $response, $args) {

    $csrf = $this->csrf->generate();

    return $this->view->render('pages/user/change_password', [
      'name' => $csrf['name'],
			'value' => $csrf['value'],
			'key' => $csrf['key']
    ]);
  }

  public function resetPassword($request, $response, $args) {

    $parsedBody = $request->getParsedBody();

    $verify = $this->jwt->verifyToken();

    if ($verify['result'] === false) {
      return $response->withJson($verify);
    }

    if (((int)$verify['data']['user_data']->role !== 1)) { // 1 = administrator
      return $response->withJson($this->message->result(false, 'You can\'t reset password!'));
    }

    $result = $this->user->resetPassword(
      $parsedBody['id'],
      $parsedBody['new_password']
    );

    return $response->withJson($result);
  }

  public function getEmployee($request, $response, $args) {
    $parsedBody = $request->getParsedBody();
    $data = $this->user->getEmployee($this->datatables->filter($parsedBody));
    $pack = $this->datatables->get($data, $parsedBody);

		return $response->withJson($pack);
    //return $response->withJson(["data" => $this->user->getEmployee()]);
  }

  public function forgotPassword($request, $response, $args) {
    try {
      $parsedBody = $request->getParsedBody();

      $filtered_email = $this->validate->isEmail($parsedBody['email']);

      $chkEmail = $this->user->chkEmail($parsedBody['email']);
      if ($chkEmail === false) {
        $this->message->addFlashMessage('error', 'E-mail is not found. '. $parsedBody['email']);
        return $response->withRedirect('/forgot_password', 301);
      }

      $nonce = $this->secure->getNonce($filtered_email);

      $message = 'เรียนผู้ใช้งาน <br/><br/>';
      $message .= 'คลิกที่ Link ด้านล่างเพื่อเปลี่ยนรหัสผ่านใหม่ <br/><br/>';
      $message .= '<a href=\'' . APP_URL . '/new_password/' . $filtered_email . '/' . $nonce . '\' >Reset Password</a>';

      $sendEmail = $this->email->sendEmail(
        'Auto Email System : Reset Password ['.$filtered_email.']',
        $message,
        [$filtered_email],
        [],
        [],
        [],
        '',
        EMAIL_USER
      );

      if ( $sendEmail ) {
        $this->message->addFlashMessage('success', 'Send mail success.');
        return $response->withRedirect('/forgot_password', 301);
      } else {
        $this->message->addFlashMessage('error', 'Send mail failed.');
        return $response->withRedirect('/forgot_password', 301);
      }
    } catch (\Exception $e) {
      $this->message->addFlashMessage('error', $e->getMessage());
      return $response->withRedirect('/forgot_password', 301);
    }
  }

  public function saveNewPassword($request, $response, $args) {
    try {
      $parsedBody = $request->getParsedBody();

      $updatePassword = $this->user->saveNewPassword($parsedBody['email'], $parsedBody['new_password']);

      $this->message->addFlashMessage('success', $updatePassword);
      return $response->withRedirect('/login', 301);
    } catch (\Exception $e) {
      $this->message->addFlashMessage('error', $e->getMessage());
      return $response->withRedirect('/login', 301);
    }
  }

  public function registerUser($request, $response, $args) {
    try {
      $parsedBody = $request->getParsedBody();

      Assert::notEmpty($parsedBody['empid']);
		  Assert::notNull($parsedBody['empid']);
      Assert::numeric($parsedBody['empid']);

      Assert::notEmpty($parsedBody['username']);
		  Assert::notNull($parsedBody['username']);
		  Assert::numeric($parsedBody['username']);

      $this->validate->isEmail($parsedBody['email']);

      Assert::notEmpty($parsedBody['password']);
      Assert::notNull($parsedBody['password']);
      Assert::greaterThanEq($parsedBody['password'], 8);

      Assert::notEmpty($parsedBody['firstname']);
      Assert::notNull($parsedBody['firstname']);
      Assert::stringNotEmpty($parsedBody['firstname']);

      Assert::notEmpty($parsedBody['lastname']);
      Assert::notNull($parsedBody['lastname']);
      Assert::stringNotEmpty($parsedBody['lastname']);

      $this->user->registerUser(
        $parsedBody['empid'],
        $parsedBody['username'],
        $parsedBody['password'],
        $parsedBody['email'],
        $parsedBody['firstname'],
        $parsedBody['lastname']
      );

      if (!file_exists('../public/assets/images/' . $parsedBody['username'])) {
        mkdir('../public/assets/images/' .$parsedBody['username'], 0777, true);
      }
      
      $this->message->addFlashMessage('success', 'Register success.');
      return $response->withRedirect('/login', 301);

    } catch (\Exception $e) {
      $this->message->addFlashMessage('error', $e->getMessage());
      return $response->withRedirect('/register', 301);
    }
  }
}
