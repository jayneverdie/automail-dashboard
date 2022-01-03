<?php

namespace App\CustomsInvoice_ByTruck;

use App\CustomsInvoice_ByTruck\CustomsInvAPI;
use App\Common\Automail;
use App\Email\EmailAPI;

class CustomsInvController
{
  private $CustApi = null;
  private $automail = null;
  private $emailApi = null;

  public function __construct()
  {
    $this->automail = new Automail();
    $this->CustApi = new CustomsInvAPI();
    $this->emailApi = new EmailAPI();
  }

  public function bytruck($request, $response, $args)
  {
    $projectId = 40;
    $root = 'files/customs_invoice/by_truck/';
    $rootTemp = 'temp/customs_invoice/by_truck/';
    $filesOkay = [];
    $fileFailed = [];

    $files = $this->automail->getDirRoot($root);

    if (count($files) === 0) 
    {
      echo "The file does not exist";
      exit();
    }

    // echo "<pre>".print_r($files,true)."</pre>";
    // exit;
    
    // NICHAPAR
    // SUNISA

    foreach ($files as $file) 
    {
      try 
      {
        $data = $this->CustApi->getCustInv($file);

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
            file_exists($root . $currentFile[1]) === true) 
        {

          $filesOkay[] = $currentFile[0];
          $filesOkay[] = $currentFile[1];

          $so = $this->CustApi->getCustSO($voucherSeries, $voucherNo);
          
          if ($so["result"] === false) {
            throw new \Exception($so["data"]);
          }

          $invno = $data["data"][1]."/".$data["data"][2]."/".$data["data"][3];
          $checkCustoms = $so["data"]["DSG_CheckerCustomer"];
          $CustomsBy = $so["data"]["DSG_CUSTOMSBY"];
          $Toport = $so["data"]["DSG_TOPORTDESC"];
          $agent = $so["data"]["DSG_DESCRIPTION"];
          $loadingDate = date("Y-m-d", strtotime($so["data"]["DSG_EDDDate"]));
          $salesId = $so["data"]["SALESID"];
          $customer = $so["data"]["SALESNAME"];

          $chkByTruck = $this->CustApi->checkByTruck($salesId);
          
          if ($chkByTruck["result"] === false) {
            throw new \Exception($chkByTruck["data"]);
          }

          $trans = $this->CustApi->getTrans($salesId);

          if (count($trans) === 0) 
          {
            $subject = $this->CustApi->getSubject($voucherSeries, $voucherNo,$customer, true);
            $attFile = [$root . $currentFile[0], $root . $currentFile[1]];     
          } 
          else 
          {
            // $subject = $this->CustApi->getSubject($voucherSeries, $voucherNo,$customer, false);
            // $attFile = [];
            throw new \Exception("ไม่สามารถส่งไฟล์ซ้ำกันได้");
          }

          $body = $this->CustApi->getBody($invno, $checkCustoms, $CustomsBy, $Toport, $agent, $loadingDate, $salesId ,$customer);

          $emails = $this->CustApi->getEmail($checkCustoms);

          // $emails = ["to" => ["wattana_r@deestone.com", "wiriya_y@deestone.com"], "cc" => ["worawut_s@deestone.com"], "sender" => "harit_j@deestone.com"];

          if ($emails === null) 
          {
            throw new \Exception("No email to send.");
          }

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

          if ($sendEmailInternal === true) 
          {

            echo "Send Email Success. <br>";

            $logging = $this->automail->logging(
              $projectId,
              'Message has been sent',
              null,
              $salesId,
              null,
              null,
              $invno,
              $file,
              'File'
            );

            $this->automail->loggingEmail($logging, $emails['to'], 1);

            $this->automail->initFolder($rootTemp, 'logs');

            $this->automail->moveFile($root, $rootTemp, 'logs/', $currentFile[0]);
            $this->automail->moveFile($root, $rootTemp, 'logs/', $currentFile[1]);
          } else 
          {
            echo $sendEmailInternal;
          }
        }

      } 
      catch (\Exception $e) 
      {

        echo $e->getMessage();

        $bodyFailed = $this->automail->getBodyReportInternalFailed([$file], "Automail Customs Invoice By Truck", $e->getMessage());
        $emailFailed = $this->CustApi->getEmailFailed();

        $sendEmailFailed = $this->emailApi->sendEmail(
          "Automail Customs Invoice By Truck : ไม่สามารถส่งไฟล์ได้",
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

    // echo "<pre>".print_r($subject ,true)."</pre>";
    // echo "<pre>".print_r($attFile ,true)."</pre>";
    // echo "<pre>".print_r($body ,true)."</pre>";
    // exit;
  }
}
