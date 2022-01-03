<?php

namespace App\FreightCompaire;

use App\FreightCompaire\FreightCompaireAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\CSRF;
use App\Common\View;

class FreightCompaireController
{
  private $CompaireApi = null;
  private $automail = null;
  private $emailApi = null;
  private $csrf = null;
  private $view = null;
  

  public function __construct()
  {
    $this->automail = new Automail();
    $this->CompaireApi = new FreightCompaireAPI();
    $this->emailApi = new EmailAPI();
    $this->csrf = new CSRF;
    $this->view = new View;
  }

  public function approvemail($request, $response, $args)
  {
    
    $projectID = 44;
    $emails = $this->CompaireApi->getEmail($projectID);
    
    $getApprove =  $this->CompaireApi->getLogApprove();

    foreach ($getApprove as $log) 
    {
        //echo "<pre>".print_r($log,true)."</pre>";
        $PI_NO = $log["QUOTATIONID"];
        $SO_NO = $log["SALESID"];
        $FreightCharge = $log["DSG_FREIGHTCHARGE"];
        $CustName = $log["SALESNAME"];
        $Port = $log["DSG_TOPORTDESC"];
        $Compaire1 = $log["DSG_COMPAIRE1"];   
        $Compaire2 = $log["DSG_COMPAIRE2"];
        $Agent1 = $log["DSG_AGENTDESC1"];       
        $Agent2 = $log["DSG_AGENTDESC2"];
        $ship1 = $log["DSG_SHIPPINGLINE1"];       
        $ship2 = $log["DSG_SHIPPINGLINE2"];
        $rate1 = $log["FEIGHTRATE1"];       
        $rate2 = $log["FEIGHTRATE2"];
        $Ens1 = $log["ENS_AMS1"];         
        $Ens2 = $log["ENS_AMS2"];
        $Lss1 = $log["LSS_CHARGE1"];         
        $Lss2 = $log["LSS_CHARGE2"];
        $War1 = $log["WARRISCKCHARGE1"];         
        $War2 = $log["WARRISCKCHARGE2"];
        $TotalUSD1 = $log["TOTALUSD1"];   
        $TotalUSD2 = $log["TOTALUSD2"];
        $Remark1 = $log["REMARK1"];
        $Remark2 = $log["REMARK2"];
        $Createby = $log["CREATEDBY"];
        $ExchangeRates = $log["EXCHANGERATES"];
        $Volume = $log["DSG_VOLUME"];
        //Rattana Chanbumrung
        $ApproveBy = $this->CompaireApi->getApproveBy();
        
        $sendby = $this->CompaireApi->getEmailSend($projectID,$Createby);

        $csrf = $this->csrf->generate();
        $nonce = $csrf['key']['csrf_value'] ;

        $subject = $this->CompaireApi->getSubject($SO_NO,$CustName);

        $body = $this->CompaireApi->getBody($PI_NO,$SO_NO,$FreightCharge,$CustName,$Port,
        $Compaire1,$Compaire2,$Agent1,$Agent2,$ship1,$ship2,$rate1,
        $rate2,$Ens1,$Ens2,$Lss1,$Lss2,$War1,$War2,$TotalUSD1,$TotalUSD2,
        $Remark1,$Remark2,$ApproveBy,$nonce,$Createby,$ExchangeRates,$Volume);

        // echo "<pre>".print_r($sendby ,true)."</pre>";
        // echo "<pre>".print_r($subject ,true)."</pre>";
        // echo "<pre>".print_r($body ,true)."</pre>";

        $sendMail = $this->emailApi->sendEmail(
            $subject,
            $body,
            $emails['to'],
            [],
            [],
            [],
            "",
            $sendby['sendby']
          );
        
        if ($sendMail === true) 
        {
            $updatetoax = $this->CompaireApi->UpdateToAX_SendApprove1(
                2,
                $PI_NO,
                $Createby,
                $SO_NO
            );

            $logging = $this->automail->logging(
              $projectID,
              'Message has been sent',
              null,
              $SO_NO,
              $PI_NO,
              null,
              null,
              null,
              'Ax'
          );

           $this->automail->loggingEmail($logging,$emails['to'],1);
           $this->CompaireApi->insertNonce($emails['to'],$nonce);
          echo "Send Email Success. <br>";

        }
        else
        {
            echo "Failed. <br>";
        }

    }
  }

  public function approvepage($request, $response, $args)
  {
      return $this->view->render('pages/FreightCompaire/approve');
  }

  public function approvecomplete($request, $response, $args)
  {
      return $this->view->render('pages/FreightCompaire/approvecomplete');
  }

  public function approve($request, $response, $args) 
  {
    try 
    {
      $projectID = 44;
      $emails = $this->CompaireApi->getEmail($projectID);

      $pi = filter_input(INPUT_POST, "pi");
      $so = filter_input(INPUT_POST, "so");
      $nonce = filter_input(INPUT_POST, "nonce");
      $status = filter_input(INPUT_POST, "approveresult");

      $FreightCharge = filter_input(INPUT_POST, "FreightCharge");
      $CustName = filter_input(INPUT_POST, "CustName");
      $Port = filter_input(INPUT_POST, "Port");
      $Compaire1 = filter_input(INPUT_POST, "Compaire1"); 
      $Compaire2 = filter_input(INPUT_POST, "Compaire2");
      $Agent1 = filter_input(INPUT_POST, "Agent1");  
      $Agent2 = filter_input(INPUT_POST, "Agent2");
      $ship1 = filter_input(INPUT_POST, "ship1");    
      $ship2 = filter_input(INPUT_POST, "ship2");
      $rate1 = filter_input(INPUT_POST, "rate1");       
      $rate2 = filter_input(INPUT_POST, "rate2");
      $Ens1 = filter_input(INPUT_POST, "Ens1");         
      $Ens2 = filter_input(INPUT_POST, "Ens2");
      $Lss1 = filter_input(INPUT_POST, "Lss1");         
      $Lss2 = filter_input(INPUT_POST, "Lss2");
      $War1 = filter_input(INPUT_POST, "War1");         
      $War2 = filter_input(INPUT_POST, "War2");
      $TotalUSD1 = filter_input(INPUT_POST, "TotalUSD1");   
      $TotalUSD2 = filter_input(INPUT_POST, "TotalUSD2");
      $Remark1 = filter_input(INPUT_POST, "Remark1");
      $Remark2 = filter_input(INPUT_POST, "Remark2");
      $Remark_Approve = filter_input(INPUT_POST, "approveremark");
      $Createby = filter_input(INPUT_POST, "Createby");
      $ExchangeRates = filter_input(INPUT_POST, "ExchangeRates");
      $Volume = filter_input(INPUT_POST, "Volume");
      
      $ApproveBy =  $this->CompaireApi->getApproveBy();
      
      $updatetoax = $this->CompaireApi->UpdateToAX_SendApprove2($pi,$status,$Createby,$so);
      $updatenonce = $this->CompaireApi->updateNonce($emails['to'],$nonce);

      $subject = $this->CompaireApi->getSubject_Complete($so,$status,$CustName);

      $body = $this->CompaireApi->getBody_SendApprove2($pi,$so,$FreightCharge,$CustName,$Port,
      $Compaire1,$Compaire2,$Agent1,$Agent2,$ship1,$ship2,$rate1,
      $rate2,$Ens1,$Ens2,$Lss1,$Lss2,$War1,$War2,$TotalUSD1,$TotalUSD2,
      $Remark1,$Remark2,$ApproveBy,$nonce,$Remark_Approve,$status,$ExchangeRates,$Volume);

      // echo json_encode(["status" => 404, "message" => $subject]);

      $sendMail = $this->emailApi->sendEmail(
        $subject,
        $body,
        $emails['tocomplete'],
        $emails['cc'],
        [],
        [],
        "",
        $emails['to'][0]
      );
    
      if ($sendMail === true) 
      {

        $logging = $this->automail->logging(
            $projectID,
            'Message has been sent',
            null,
            $so,
            $pi,
            null,
            null,
            null,
            'Web'
          );

          $this->automail->loggingEmail($logging,$emails['tocomplete'],1);
        
      }

      echo json_encode(["status" => 200, "message" => "Approve Successful"]);
    
    } 
    catch (Exception $e) 
    {
      echo $e->getMessage();
    } 
  }

  
}