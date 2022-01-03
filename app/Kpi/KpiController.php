<?php

namespace App\Kpi;

use App\Common\View;
use App\Kpi\KpiAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;
use App\Common\CSRF;

class KpiController {

	public function __construct() {
		$this->view = new View;
		$this->kpi = new KpiAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
        $this->csrf = new CSRF;
	}

    public function waitingView($request, $response, $args) {
        return $this->view->render('pages/Approve/waiting');
    }

    public function approveWaiting($request, $response, $args) {
        try {
            $parsedBody = $request->getParsedBody();

            $soall = explode(",", $parsedBody["soall"]);
            
            for ($i=0; $i < count($soall); $i++) { 
                // echo $soall[$i]."X".$parsedBody["approve".$soall[$i]]."Y".$parsedBody["remarkapprove".$soall[$i]]."\n";
                $all[] = [
                    "SO" =>  $soall[$i],
                    "APPROVE" => $parsedBody["approve".$soall[$i]],
                    "REMARK" => $parsedBody["remarkapprove".$soall[$i]]
                ];
            }
            // var_dump($all);
            $so_approve=[];
            $so_reject=[];

            foreach ($all as $key => $value) {
                if ($value['APPROVE']==1) {
                    $so_approve[] = [
                        "SO" =>  $value['SO'],
                        "REMARK" => ""
                    ];
                }else{
                    $so_reject[] = [
                        "SO" =>  $value['SO'],
                        "REMARK" => $value['REMARK']
                    ];
                }
            }

            // var_dump($so_approve);
            // var_dump($so_reject);
            $so_a=[]; 
            $so_r=[];
            $sendApproveConfirm='';
            $sendRejectConfirm='';

            if (count($so_approve)>0) {
                foreach ($so_approve as $value) {
                    array_push($so_a, $value['SO']);
                    // $this->kpi->updateStatus(2,1,$value['SO']);
                }
                $so_a_str = implode($so_a, ",");
                $sendApproveConfirm = self::sendApproveConfirm($so_a_str,$parsedBody["userid"],$so_a,$parsedBody["nonce"]);
            }

            if (count($so_reject)>0) {
                foreach ($so_reject as $value) {
                    array_push($so_r, $value['SO']);
                    // $this->kpi->updateStatus(3,1,$value['SO']);
                }
                $so_r_str = implode($so_r, ",");
                $sendRejectConfirm = self::sendRejectConfirm($so_r_str,$parsedBody["userid"],$so_reject,$parsedBody["nonce"]);
            }
            
            if ($sendApproveConfirm===false) {
                echo json_encode(["status" => 404, "message" => "Approve Failed !"]);
                // exit();
            }

            if ($sendRejectConfirm===false) {
                echo json_encode(["status" => 404, "message" => "Approve Reject Failed !"]);
                // exit();
            }

            // echo $sendApproveConfirm;
            echo json_encode(["status" => 200, "message" => "Approve Successful"]);

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function sendApprove($request, $response, $args) {
        try {
            
            $emailConfirm = $this->kpi->getEmailConfirm();
            $emailApprove = $this->kpi->getEmailApprove();
            
            $today = date('Y-m-d');
            $stauts = 1;
            $emailConfirmed = [];

            foreach ($emailConfirm as $e) {
                if ($this->kpi->mapSoConfirm($today,$stauts,$e['USERID']) === true) {
                    $emailConfirmed[] = [
                        'userid' => $e['USERID'],
                        'email' => $e['EMAIL']
                    ];
                }
            }

            if (count($emailConfirmed) > 0) {
                for ($i=0; $i < count($emailConfirmed); $i++) { 
                
                        $listSOWaiting = $this->kpi->getSo($today,1,$emailConfirmed[$i]['userid']);
                        $listSORevised = $this->kpi->getSo($today,2,$emailConfirmed[$i]['userid']);

                        $listDocNumWaiting = $this->kpi->getSo($today,3,$emailConfirmed[$i]['userid']);
                        $listDocNumRevised = $this->kpi->getSo($today,4,$emailConfirmed[$i]['userid']);
                        $listDocNumLogsWait = $this->kpi->getLogs($emailConfirmed[$i]['userid'],1,$today);
                        $listDocNumLogsRevised = $this->kpi->getLogs($emailConfirmed[$i]['userid'],2,$today);
                        
                        // if (count($listDocNumWaiting) >= count($listDocNumLogsWait)) {
                            $result_wait = array_diff($listDocNumWaiting,$listDocNumLogsWait);
                        // }else{
                        //     $result_wait = array_diff($listDocNumLogsWait,$listDocNumWaiting);
                        // }

                        // if (count($listDocNumLogsRevised) <= count($listDocNumRevised)) {
                            $result_revised = array_diff($listDocNumRevised,$listDocNumLogsRevised);
                        // }else{
                        //     $result_revised = array_diff($listDocNumLogsRevised,$listDocNumRevised);
                        // }

                        // echo "REVISED"."<br>";
                        // print_r($listDocNumRevised);
                        // echo "<hr>";
                        // print_r($listDocNumLogsRevised);
                        // echo "<hr>";
                        // print_r($result_revised);
                        // echo "<br>";

                        // echo "WAITING"."<br>";
                        // print_r($listDocNumWaiting);
                        // echo "<hr>";
                        // print_r($listDocNumLogsWait);
                        // echo "<hr>";
                        // print_r($result_wait);
                        // echo "<br>";

                        // exit();

                        $csrf = $this->csrf->generate();
                        if (count($result_revised)>0) {
                            if (count($listSORevised)>0) {
                                // echo "REVISED : ".$emailConfirmed[$i]['userid']."->".$emailConfirmed[$i]['email'];
                                // echo "<br>";

                                // echo $this->kpi->getKpiSubject('revised');
                                // echo "<br>";

                                // echo $this->kpi->getKpiBody($today,$stauts,$emailConfirmed[$i]['userid'],$csrf['key']['csrf_value'],$listSORevised);
                                // echo "<hr>";

                                $sendEmail = $this->email->sendEmail(
                                    $this->kpi->getKpiSubject('revised'),
                                    $this->kpi->getKpiBody($today,$stauts,$emailConfirmed[$i]['userid'],$csrf['key']['csrf_value'],$listSORevised),
                                    [$emailApprove[0]['EMAIL']],
                                    // ['harit_j@deestone.com'],
                                    [],
                                    [],
                                    [],
                                    $emailConfirmed[$i]['email'],
                                    $emailConfirmed[$i]['email']
                                );

                                if($sendEmail === true) {
                                    echo "Revised Message has been send. \n";
                                    $this->kpi->insertNonce($emailConfirmed[$i]['email'],$csrf['key']['csrf_value']);
                                    $this->kpi->insertLogs($emailConfirmed[$i]['userid'],$result_revised,2);
                                }
                            }
                        }else{
                            echo "Revised is not send.\n";
                        }

                        if (count($result_wait)>0) {
                            if (count($listSOWaiting)>0) {
                                // echo "WAITING : ". $emailConfirmed[$i]['userid']."->".$emailConfirmed[$i]['email'];
                                // echo "<br>";

                                // echo $this->kpi->getKpiSubject('waiting');
                                // echo "<br>";

                                // echo $this->kpi->getKpiBody($today,$stauts,$emailConfirmed[$i]['userid'],$csrf['key']['csrf_value'],$listSOWaiting);
                                // echo "<hr>";

                                $sendEmail = $this->email->sendEmail(
                                    $this->kpi->getKpiSubject('waiting'),
                                    $this->kpi->getKpiBody($today,$stauts,$emailConfirmed[$i]['userid'],$csrf['key']['csrf_value'],$listSOWaiting),
                                    [$emailApprove[0]['EMAIL']],
                                    // ['harit_j@deestone.com'],
                                    [],
                                    [],
                                    [],
                                    $emailConfirmed[$i]['email'],
                                    $emailConfirmed[$i]['email']
                                );

                                if($sendEmail === true) {
                                    echo "Waiting Message has been send. \n";
                                    $this->kpi->insertNonce($emailConfirmed[$i]['email'],$csrf['key']['csrf_value']);
                                    $this->kpi->insertLogs($emailConfirmed[$i]['userid'],$result_wait,1);
                                }
                            }
                        }else{
                            echo "Waiting is not send.\n";
                        }

                }
            }else{

                echo "So Confirm not found is today.";
                exit();
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function sendApproveConfirm($so,$userid,$so_a=[],$nonce) {
        try {

            $emailApprove = $this->kpi->getEmailApprove();
            $emailConfirm = $this->kpi->getEmailConfirmBy($userid);

            $so  = '';
            foreach ($so_a as $s) {
                $so .= "'".$s."'" . ', ';
            }
            $so = trim($so, ', ');

            $getBodyBySo = $this->kpi->getBodyBySo($so);

            $sendEmail = $this->email->sendEmail(
                "Confirmation SO Approve Online (".$so.")",
                "SO Confirmation <br> Status : Approved  <br><br> SO Confirmation Approved : ".$so."<br><br>".$getBodyBySo,
                [$emailConfirm[0]['EMAIL']],
                [],
                [],
                [],
                $emailApprove[0]['EMAIL'],
                $emailApprove[0]['EMAIL']
            );

            if($sendEmail === true) {
                $this->kpi->updateStatus(2,1,$so);
                $this->kpi->updateNonce($emailConfirm[0]['EMAIL'],$nonce);
                return true;
            }else{
                return false;
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function sendRejectConfirm($so,$userid,$so_reject=[],$nonce) {
        try {

            $emailApprove = $this->kpi->getEmailApprove();
            $emailConfirm = $this->kpi->getEmailConfirmBy($userid);
            
            $so  = '';
            $txt = '';
            foreach ($so_reject as $r) {
                $so .= "'".$r['SO']."'" . ', ';
                $txt .= $r['SO']." : ".$r['REMARK'];
                $txt .= "<br>";
            }
            $so = trim($so, ', ');
            
            $getBodyBySo = $this->kpi->getBodyBySo($so);

            $sendEmail = $this->email->sendEmail(
                "Confirmation SO Approve Online (".$so.")",
                "SO Confirmation <br> Status : Reject  <br><br> SO Confirmation Reject : <br>".$txt."<br><br>".$getBodyBySo,
                [$emailConfirm[0]['EMAIL']],
                [],
                [],
                [],
                $emailApprove[0]['EMAIL'],
                $emailApprove[0]['EMAIL']
            );

            if($sendEmail === true) {
                $this->kpi->updateStatus(3,1,$so);
                $this->kpi->updateNonce($emailConfirm[0]['EMAIL'],$nonce);
                return true;
            }else{
                return false;
            }

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}
