<?php

namespace App\Trucking_Agen;

use App\Trucking_Agen\TruckingAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\CSRF;
use App\Common\View;

class TruckingController
{
  private $CompaireApi = null;
  private $automail = null;
  private $emailApi = null;
  private $csrf = null;
  private $view = null;


  public function __construct()
  {
    $this->automail = new Automail();
    $this->CompaireApi = new TruckingAPI();
    $this->emailApi = new EmailAPI();
    $this->csrf = new CSRF;
    $this->view = new View;
  }

  public function approvemail($request, $response, $args)
  {


    $sendby = $this->CompaireApi->getEmailSend(46);


    $getApproveSMILE =  $this->CompaireApi->getTrucking($type = 'SMILE');
    $getApproveMAXX =  $this->CompaireApi->getTrucking($type = 'MAXX');
    $getApproveJCK =  $this->CompaireApi->getTrucking($type = 'JCK');
    $getApproveSONIC =  $this->CompaireApi->getTrucking($type = 'SONIC');
    // print_r($getApprove);
    // exit();
    $subject = $this->CompaireApi->getSubject();


    //echo "<pre>" . print_r($getApproveSMILE, true) . "</pre>";
    // echo "<pre>" . print_r($subject, true) . "</pre>";
    //echo "<pre>" . print_r($subject, true) . "</pre>";
    //echo "<pre>" . print_r($emails, true) . "</pre>";
    //exit();

    // send Mail SMILE
    if (count($getApproveSMILE) > 0) {
      $emails = $this->CompaireApi->getEmail(46);
      $bodySMILE = $this->CompaireApi->getBody(
        $getApproveSMILE

      );
      // echo $bodySMILE;
      // exit();
      $sendMail = $this->emailApi->sendEmail(
        $subject,
        $bodySMILE,
        $emails['to'],
        $emails['cc'],
        [],
        [],
        $sendby['sendby'],
        $sendby['sendby']
      );

      if ($sendMail === true) {

        $logging = $this->automail->logging(
          46,
          'Message has been sent',
          null,
          null,
          null,
          null,
          null,
          null,
          'Ax'
        );

        $this->automail->loggingEmail($logging, $emails['to'], 1);

        echo "Send Email Success. <br>";
      } else {
        echo "Failed. <br>";
      }
    }

    // exit();
    // send Mail MAXX
    if (count($getApproveMAXX) > 0) {
      $emails = $this->CompaireApi->getEmail(47);
      $bodyMAXX = $this->CompaireApi->getBody(
        $getApproveMAXX

      );

      $sendMail = $this->emailApi->sendEmail(
        $subject,
        $bodyMAXX,
        $emails['to'],
        $emails['cc'],
        [],
        [],
        $sendby['sendby'],
        $sendby['sendby']
      );

      if ($sendMail === true) {

        $logging = $this->automail->logging(
          47,
          'Message has been sent',
          null,
          null,
          null,
          null,
          null,
          null,
          'Ax'
        );

        $this->automail->loggingEmail($logging, $emails['to'], 1);

        echo "Send Email Success. <br>";
      } else {
        echo "Failed. <br>";
      }
    }
    // send Mail JCK
    if (count($getApproveJCK) > 0) {
      $emails = $this->CompaireApi->getEmail(48);
      $bodyJCK = $this->CompaireApi->getBody(
        $getApproveJCK

      );

      $sendMail = $this->emailApi->sendEmail(
        $subject,
        $bodyJCK,
        $emails['to'],
        $emails['cc'],
        [],
        [],
        $sendby['sendby'],
        $sendby['sendby']
      );

      if ($sendMail === true) {

        $logging = $this->automail->logging(
          48,
          'Message has been sent',
          null,
          null,
          null,
          null,
          null,
          null,
          'Ax'
        );

        $this->automail->loggingEmail($logging, $emails['to'], 1);

        echo "Send Email Success. <br>";
      } else {
        echo "Failed. <br>";
      }
    }

    if (count($getApproveSONIC) > 0) {
      $emails = $this->CompaireApi->getEmail(49);
      $bodySONIC = $this->CompaireApi->getBody(
        $getApproveSONIC

      );
      // send Mail SONIC
      $sendMail = $this->emailApi->sendEmail(
        $subject,
        $bodySONIC,
        $emails['to'],
        $emails['cc'],
        [],
        [],
        $sendby['sendby'],
        $sendby['sendby']
      );

      if ($sendMail === true) {

        $logging = $this->automail->logging(
          49,
          'Message has been sent',
          null,
          null,
          null,
          null,
          null,
          null,
          'Ax'
        );

        $this->automail->loggingEmail($logging, $emails['to'], 1);

        echo "Send Email Success. <br>";
      } else {
        echo "Failed. <br>";
      }
    }
  }
}
