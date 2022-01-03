<?php

namespace App\ShortShip;

use App\ShortShip\ShortShipAPI;
use App\Common\Automail;
use App\Email\EmailAPI;

class ShortShipController
{
  private $shortShipApi = null;
  private $automail = null;
  private $emailApi = null;

  public function __construct()
  {
    $this->automail = new Automail();
    $this->shortShipApi = new ShortShipAPI();
    $this->emailApi = new EmailAPI();
  }

  public function send($request, $response, $args)
  {
    $projectId = 39;
    $root = 'files/shortship/';
    $rootTemp = 'temp/shortship/';
    $filesOkay = [];
    $fileFailed = [];

    $files = $this->automail->getDirRoot($root);

    if (count($files) === 0) {
      echo "The file does not exist";
      exit();
    }

    // NICHAPAR
    // SUNISA

    foreach ($files as $file) {
      try {
        $data = $this->shortShipApi->getShortShipInv($file);

        $currentFile = [];

        $voucherNo = $data["data"][3];
        $voucherSeries = $data["data"][2];

        if ($data["data"][0] === "INV")
        {
          $currentFile = [$file, str_replace("INV", "PK", $file)];
        }

        if ($data["data"][0] === "PK")
        {
          $currentFile = [$file, str_replace("PK", "INV", $file)];
        }


        if ($data["result"] === true && 
            in_array($file, $filesOkay) === false && 
            file_exists($root . $currentFile[0]) === true &&
            file_exists($root . $currentFile[1]) === true) {

          $filesOkay[] = $currentFile[0];
          $filesOkay[] = $currentFile[1];

         
          $so = $this->shortShipApi->getShortShipSO($voucherSeries, $voucherNo);
          
          if ($so["result"] === false) {
            throw new \Exception($so["data"]);
          }

          // Test Mode Setup
          $testMode = false;

          $checkCustoms = $so["data"]["DSG_CheckerCustomer"];
          $checkCustomsBy = $so["data"]["DSG_CustomsBy"];

          $loadingDate = date("Y-m-d", strtotime($so["data"]["DSG_EDDDate"]));
          $salesId = $so["data"]["SALESID"];

          $trans = $this->shortShipApi->getTrans($voucherSeries, $voucherNo);

          // var_dump($trans); exit;

          // if (count($trans) === 0) {
          //   $noShort = true;
          //   $subject = $this->shortShipApi->getSubject($voucherSeries, $voucherNo, true, $testMode);
          //   $attFile = [];
          // } else {
          //   $noShort = false;
          //   $subject = $this->shortShipApi->getSubject($voucherSeries, $voucherNo, false, $testMode);
          //   $attFile = [$root . $currentFile[0], $root . $currentFile[1]];
          // }

          if (count($trans) === 0) {
            $noShort = true;
            $subject = $this->shortShipApi->getSubject($voucherSeries, $voucherNo, true, $testMode);
            $attFile = [];
          } else {


            $confirmNoShort = 0;
            foreach ($trans as $t) {
              if ((int)$t["SHIPPED"] > 0) {
                $confirmNoShort++;
                break;
              }
            }

            if ($confirmNoShort === 0) { // diff === 0
              $noShort = true;
              $subject = $this->shortShipApi->getSubject($voucherSeries, $voucherNo, true, $testMode);
              $attFile = [];
            } else {
              $noShort = false;
              $subject = $this->shortShipApi->getSubject($voucherSeries, $voucherNo, false, $testMode);
              $attFile = [$root . $currentFile[0], $root . $currentFile[1]];
            }
          }

          // var_dump($noShort);

          // exit;

          $body = $this->shortShipApi->getBody($voucherSeries, $voucherNo, $loadingDate, $checkCustoms, $checkCustomsBy, $trans, $noShort);
          $emails = $this->shortShipApi->getEmail($checkCustoms, $testMode);
          // $emails = ["to" => ["wattana_r@deestone.com", "wiriya_y@deestone.com"], "cc" => ["worawut_s@deestone.com"], "sender" => "harit_j@deestone.com"];

          if ($emails === null) {
            throw new \Exception("No email to send.");
          }

          // var_dump($body); exit;

          $sendEmailInternal = $this->emailApi->sendEmail(
            $subject,
            $body,
            $emails['to'],
            $emails['cc'],
            [],
            $attFile,
            "",
            $emails['sender']
          );

          if ($sendEmailInternal === true) {

            echo "Send Email Success. <br>";

            $logging = $this->automail->logging(
              $projectId,
              'Message has been sent',
              null,
              $salesId,
              null,
              null,
              null,
              $file,
              'File'
            );

            $this->automail->loggingEmail($logging, $emails['to'], 1);

            $this->automail->initFolder($rootTemp, 'logs');

            $this->automail->moveFile($root, $rootTemp, 'logs/', $currentFile[0]);
            $this->automail->moveFile($root, $rootTemp, 'logs/', $currentFile[1]);
          } else {

            echo $sendEmailInternal;
          }
        }
      } catch (\Exception $e) {

        echo $e->getMessage();
        // exit;

        $bodyFailed = $this->automail->getBodyReportInternalFailed([$file], "Automail Short Ship", $e->getMessage());
        $emailFailed = $this->shortShipApi->getEmailFailed();

        $sendEmailFailed = $this->emailApi->sendEmail(
          "Automail Short Ship : ไม่สามารถส่งไฟล์ได้",
          $bodyFailed,
          $emailFailed['to'],
          [],
          [],
          [$root . $currentFile[0], $root . $currentFile[1]],
          "",
          ""
        );

        if ($sendEmailFailed === true) {
          $this->automail->initFolder($rootTemp, 'failed');
          $this->automail->moveFile($root, $rootTemp, 'failed/', $currentFile[0]);
          $this->automail->moveFile($root, $rootTemp, 'failed/', $currentFile[1]);
        }
      }
    }
  }

  // TEST SEND EMAIL = set $testMode = true
  public function send_test($request, $response, $args)
  {
    $projectId = 39;
    $root = 'D:\\TEST_AUTOMAIL\\';
    $rootTemp = 'temp/shortship/';
    $filesOkay = [];
    $fileFailed = [];

    $files = $this->automail->getDirRoot($root);

    if (count($files) === 0) {
      echo "The file does not exist";
      exit();
    }

    // NICHAPAR
    // SUNISA

    foreach ($files as $file) {
      try {
        $data = $this->shortShipApi->getShortShipInv($file);

        $currentFile = [];

        $voucherNo = $data["data"][3];
        $voucherSeries = $data["data"][2];

        if ($data["data"][0] === "INV")
        {
          $currentFile = [$file, str_replace("INV", "PK", $file)];
        }

        if ($data["data"][0] === "PK")
        {
          $currentFile = [$file, str_replace("PK", "INV", $file)];
        }


        if ($data["result"] === true && 
            in_array($file, $filesOkay) === false && 
            file_exists($root . $currentFile[0]) === true &&
            file_exists($root . $currentFile[1]) === true) {

          $filesOkay[] = $currentFile[0];
          $filesOkay[] = $currentFile[1];

         
          $so = $this->shortShipApi->getShortShipSO($voucherSeries, $voucherNo);
          
          if ($so["result"] === false) {
            throw new \Exception($so["data"]);
          }

          // Test Mode Setup
          $testMode = true;

          $checkCustoms = $so["data"]["DSG_CheckerCustomer"];
          $checkCustomsBy = $so["data"]["DSG_CustomsBy"];

          $loadingDate = date("Y-m-d", strtotime($so["data"]["DSG_EDDDate"]));
          $salesId = $so["data"]["SALESID"];

          $trans = $this->shortShipApi->getTrans($voucherSeries, $voucherNo);

          

          if (count($trans) === 0) {
            $noShort = true;
            $subject = $this->shortShipApi->getSubject($voucherSeries, $voucherNo, true, $testMode);
            $attFile = [];
          } else {


            $confirmNoShort = 0;
            foreach ($trans as $t) {
              if ((int)$t["SHIPPED"] > 0) {
                $confirmNoShort++;
                break;
              }
            }

            if ($confirmNoShort === 0) { // diff === 0
              $noShort = true;
              $subject = $this->shortShipApi->getSubject($voucherSeries, $voucherNo, true, $testMode);
              $attFile = [];
            } else {
              $noShort = false;
              $subject = $this->shortShipApi->getSubject($voucherSeries, $voucherNo, false, $testMode);
              $attFile = [$root . $currentFile[0], $root . $currentFile[1]];
            }
          }

          $body = $this->shortShipApi->getBody($voucherSeries, $voucherNo, $loadingDate, $checkCustoms, $checkCustomsBy, $trans, $noShort);
          $emails = $this->shortShipApi->getEmail($checkCustoms, $testMode);
          // $emails = ["to" => ["wattana_r@deestone.com", "wiriya_y@deestone.com"], "cc" => ["worawut_s@deestone.com"], "sender" => "harit_j@deestone.com"];

          if ($emails === null) {
            throw new \Exception("No email to send.");
          }

          // var_dump([$subject, $emails, $attFile]); exit;

          $sendEmailInternal = $this->emailApi->sendEmail(
            $subject,
            $body,
            $emails['to'],
            $emails['cc'],
            [],
            $attFile,
            "",
            $emails['sender']
          );

          if ($sendEmailInternal === true) {

            echo "Send Email Success. <br>";

            $logging = $this->automail->logging(
              $projectId,
              'Message has been sent',
              null,
              $salesId,
              null,
              null,
              null,
              $file,
              'File'
            );

            $this->automail->loggingEmail($logging, $emails['to'], 1);

            $this->automail->initFolder($rootTemp, 'logs');

            $this->automail->moveFile($root, $rootTemp, 'logs/', $currentFile[0]);
            $this->automail->moveFile($root, $rootTemp, 'logs/', $currentFile[1]);
          } else {

            echo $sendEmailInternal;
          }
        }
      } catch (\Exception $e) {

        echo $e->getMessage();
        // exit;

        $bodyFailed = $this->automail->getBodyReportInternalFailed([$file], "Automail Short Ship", $e->getMessage());
        $emailFailed = $this->shortShipApi->getEmailFailed();

        $sendEmailFailed = $this->emailApi->sendEmail(
          "Automail Short Ship : ไม่สามารถส่งไฟล์ได้",
          $bodyFailed,
          $emailFailed['to'],
          [],
          [],
          [$root . $currentFile[0], $root . $currentFile[1]],
          "",
          ""
        );

        if ($sendEmailFailed === true) {
          $this->automail->initFolder($rootTemp, 'failed');
          $this->automail->moveFile($root, $rootTemp, 'failed/', $currentFile[0]);
          $this->automail->moveFile($root, $rootTemp, 'failed/', $currentFile[1]);
        }
      }
    }
  }
}
