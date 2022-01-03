<?php

namespace App\Camso;

use App\Common\View;
use App\Camso\CamsoAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;

class CamsoController {

	public function __construct() {
		$this->view = new View;
		$this->camso = new CamsoAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}

	public function runCamso($request, $response, $args) {
		try {
			
			$email_dev = ['to' => ['harit_j@deestone.com'], 'cc' => ['wattana_r@deestone.com']];
			$email_internal_dev = ['to' => ['wattana_r@deestone.com'], 'cc' => []];
			$acc_fin_dev = ['to' => ['harit_j@deestone.com'], 'cc' => []];

			$acc_fin = [
				'to_fin' => [	
					"alintheeta_w@deestone.com",
				    "pramate_p@deestone.com",
				    "boonreon_r@deestone.com",
				    "jaruphan_i@deestone.com",
				    "kannika_w@deestone.com",
				    "jirapat_p@deestone.com",
				    "salinee_p@deestone.com",
				    "kritsana_p@deestone.com"
				],
				"to_acc" => [
				    "chiraporn_o@deestone.com",
				    "kanittha_s@deestone.com",
				    "netdow_a@deestone.com",
				    "sukanya_a@deestone.com",
				    "sarocha_m@deestone.com"
				]
			];

			$parsedBody = $request->getParsedBody();
			$params = $request->getQueryParams();
			$custcode = $parsedBody["custcode"];
			// exit();
			$stackFileByCustCode = [];
			$stackFileFailed = [];
			$stackCamsoLoadstar_FAIL = [];
			$listOfMailDetail = [];
			$mailData = [];

			if ($custcode === "C-1441") {
				$projectId = 7;
				$_custcode = "C-1441";
				$root = 'files/camso/C-1441/';
				$rootTemp = 'temp/camso/C-1441/';
				$_subjectMailCust = 'CAMSO LOADSTAR (PVT) LTD (PV3541)';
			} else if ($custcode === "C-2536") {
				$projectId = 8;
				$_custcode = "C-2536";
				$root = 'files/camso/C-2536/';
				$rootTemp = 'temp/camso/C-2536/';
				$_subjectMailCust = 'CAMSO TRADING (Private) LIMITED';
			}else if ($custcode === "ISF"){
				$projectId = 9;
				$_custcode = "C-2536";
				$root = 'files/camso/ISF/';
				$rootTemp = 'temp/camso/ISF/';
				$_subjectMailCust = 'CAMSO TRADING (Private) LIMITED';
			}

			$files = $this->automail->getDirRoot($root);

			if (count($files)===0) {
				echo "The file does not exist";
				exit();
			}
			
			foreach ($files as $f) {
				
				if ($this->camso->checkFileExist($f,$projectId)===true) {
				
					$this->automail->initFolder($rootTemp, 'exists');
					$this->automail->moveFile($root, $rootTemp, 'exists/', $f);

				}else{

					$dataFromFileName = $this->camso->getDataFromInvoice($f);
					$quantationCamso  = $this->camso->getCamsoFullQuantation($f); 

					// echo "<pre>".print_r($quantationCamso,true)."</pre>";

					if ($custcode !== 'C-1441') 
					{
						$dataFromFileName['DSG_SHIPPINGMARK'] = '';
					}

					if ($custcode === 'C-2536') 
					{
						$_shippingMark = $this->camso->newLineShippingMark($dataFromFileName['DSG_SHIPPINGMARK']);
					}
					else
					{
						$_shippingMark = $dataFromFileName['DSG_SHIPPINGMARK'];
					}

					$state = $this->camso->getStateAndPort($this->camso->getQaNoSharp($quantationCamso), $_custcode);
					// echo $state[0]['DSG_STATE'];
					// echo "\n";
					// echo $state[0]['Port'];
					// exit();
					if (count($state) !== 0) {
						if (count($dataFromFileName) !== 0 && $this->camso->getCustomerCode($f) === $_custcode) {
							
							$mailCustomer = $this->camso->getMailCustomer($projectId,$state[0]['DSG_STATE'],$state[0]['Port']);
							// echo "<pre>".print_r($state,true)."</pre>";
							// echo "<pre>".print_r($mailCustomer,true)."</pre>";

							$mailData[] = [
								'file' => [$root.$f],
								'filename' => [$f],
								'quantation' => $quantationCamso,
								'mail_to' => $mailCustomer['to'],
								'mail_cc' => $mailCustomer['cc'],
								'mail_to_internal' => $mailCustomer['internal'],
								'mail_sender' => $mailCustomer['sender'],
								'data' => [
									'invoice' => strtoupper($dataFromFileName['DATAAREAID']) . '/' . $dataFromFileName['DSG_VOUCHERSERIES'] . '/' . $dataFromFileName['DSG_VOUCHERNO'],
									'lc_no' => $dataFromFileName['DSG_LC_NO'],
									'issue_bank' => $dataFromFileName['DSG_ISSUEDBANK'],
									'shipping_mark' => $_shippingMark,
									'cust_ref' => $dataFromFileName['CUSTOMERREF'],
									'cust_name' => $_subjectMailCust,
									'issue_date' => $dataFromFileName['DSG_LC_DATE1'],
									'country' => $state[0]['Country'],
									'state' => $state[0]['DSG_STATE'],
									'port' => $state[0]['Port']
								]
							];

							// echo "<pre>".print_r($mailData,true)."</pre>";
						}else{
							$stackCamsoLoadstar_FAIL[] = $f;
						}
					}

				}
			}
			// echo "<pre>".print_r($mailData,true)."</pre>";
			// exit();
			
			if (count($mailData) !== 0) {
				
				foreach ($mailData as $value) {

					// send to external
					$sendEmail = $this->email->sendEmail(
						$this->camso->getCamsoSubject($value['quantation'], $value['data']['cust_name'], $custcode),
						$this->camso->getCamsoBody(
							$value['data']['cust_ref'], 
							$value['quantation'], 
							$value['data']["invoice"], 
							$value['data']["lc_no"], 
							str_replace('\n', '<br>', $value['data']["shipping_mark"]), 
							$value['data']["issue_bank"],
							$value['data']["issue_date"],
							$value['data']['cust_name'],
							$custcode
						),
						$value['mail_to'], 
						$value['mail_cc'], 
						// $email_dev['to'],
						// $email_dev['cc'],
						[], 
						[$root . $value['filename'][0]],
						$value['mail_sender'][0],
						$value['mail_sender'][0]
					);

					if($sendEmail === true) {
						echo "Message has been sent External\n";
						
						// insert logs
						$logging = $this->automail->logging(
							$projectId,
							'Message has been sent',
							$custcode,
							null,
							null,
							$value['quantation'],
							$value['data']["invoice"],
							$value['filename'][0],
							'file'
						);

						$this->automail->loggingEmail($logging,$value['mail_to'],1);
						$this->automail->loggingEmail($logging,$value['mail_cc'],2);

						// send to internal
						$sendEmailInternal = $this->email->sendEmail(
							$this->camso->getCamsoSubject($value['quantation'], $value['data']['cust_name'], $custcode),
							$this->camso->getCamsoBody(
								$value['data']['cust_ref'], 
								$value['quantation'], 
								$value['data']["invoice"], 
								$value['data']["lc_no"], 
								str_replace('\n', '<br>', $value['data']["shipping_mark"]), 
								$value['data']["issue_bank"],
								$value['data']["issue_date"],
								$value['data']['cust_name'],
								$custcode
							),
							$value['mail_to_internal'], 
							// $email_internal_dev['to'],
							[], 
							[], 
							[$root . $value['filename'][0]],
							$value['mail_sender'][0],
							$value['mail_sender'][0]
						);

						if($sendEmailInternal === true) {
							echo "Message has been sent internal\n";
						}else{
							echo $sendEmailInternal;
						}

						$checkDocsInFileName = $this->camso->isDocsInFileName($value['filename']);

						if (count($checkDocsInFileName) !== 0) {
							// send to fin
							$sendEmailFin = $this->email->sendEmail(
								$this->camso->getCamsoSubject($value['quantation'], $value['data']['cust_name'], $custcode),
								$this->camso->getCamsoBody(
									$value['data']['cust_ref'], 
									$value['quantation'], 
									$value['data']["invoice"], 
									$value['data']["lc_no"], 
									str_replace('\n', '<br>', $value['data']["shipping_mark"]), 
									$value['data']["issue_bank"],
									$value['data']["issue_date"],
									$value['data']['cust_name'],
									$custcode
								),
								$acc_fin['to_fin'], 
								// $acc_fin_dev['to'],
								[], 
								[], 
								[$root . $value['filename'][0]],
								$value['mail_sender'][0],
								$value['mail_sender'][0]
							);
							
							if($sendEmailFin === true) {
								echo "Message has been sent Fin\n";
							}else{
								echo $sendEmailFin;
							}
							// send to acc
							$sendEmailAcc = $this->email->sendEmail(
								$this->camso->getCamsoSubject($value['quantation'], $value['data']['cust_name'], $custcode),
								$this->camso->getCamsoBody(
									$value['data']['cust_ref'], 
									$value['quantation'], 
									$value['data']["invoice"], 
									$value['data']["lc_no"], 
									str_replace('\n', '<br>', $value['data']["shipping_mark"]), 
									$value['data']["issue_bank"],
									$value['data']["issue_date"],
									$value['data']['cust_name'],
									$custcode
								),
								$acc_fin['to_acc'], 
								// $acc_fin_dev['to'],
								[], 
								[], 
								[$root . $value['filename'][0]],
								$value['mail_sender'][0],
								$value['mail_sender'][0]
							);
							
							if($sendEmailAcc === true) {
								echo "Message has been sent Acc\n";
							}else{
								echo $sendEmailAcc;
							}
						}

						// sendSucess movefile
						$this->automail->initFolder($rootTemp, 'logs');
						$this->automail->moveFile($root, $rootTemp, 'logs/', $value['filename'][0]);

					}else{
						echo $sendEmail;
						// sendfailed movefile
						$this->automail->initFolder($root, 'failed');
						$this->automail->moveFile($root, $root, 'failed/', $value['filename'][0]);
					}
					
				}
				
			}

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function runCamsoACC($request, $response, $args) {
		try {
			
			$email_dev = ['to' => ['harit_j@deestone.com'], 'cc' => ['wattana_r@deestone.com']];
			$email_internal_dev = ['to' => ['wattana_r@deestone.com'], 'cc' => []];
			$acc_fin_dev = ['to' => ['harit_j@deestone.com'], 'cc' => []];
			
			$acc_fin = [
				'to_fin' => [	
					"alintheeta_w@deestone.com",
				    "pramate_p@deestone.com",
				    "boonreon_r@deestone.com",
				    "jaruphan_i@deestone.com",
				    "kannika_w@deestone.com",
				    "jirapat_p@deestone.com",
				    "salinee_p@deestone.com",
				    "kritsana_p@deestone.com"
				],
				"to_acc" => [
				    "chiraporn_o@deestone.com",
				    "kanittha_s@deestone.com",
				    "netdow_a@deestone.com",
				    "sukanya_a@deestone.com",
				    "sarocha_m@deestone.com",
				    "harit_j@deestone.com",
				    "pattayanee_r@deestone.com"
				]
			];

			$parsedBody = $request->getParsedBody();
			$params = $request->getQueryParams();
			$custcode = $parsedBody["custcode"];
			// exit();
			$stackFileByCustCode = [];
			$stackFileFailed = [];
			$stackCamsoLoadstar_FAIL = [];
			$listOfMailDetail = [];
			$mailData = [];

			if ($custcode === "C-1441") {
				$projectId = 7;
				$_custcode = "C-1441";
				$root = 'files/camso_acc/C-1441/';
				$rootTemp = 'temp/camso_acc/C-1441/';
				$_subjectMailCust = 'CAMSO LOADSTAR (PVT) LTD (PV3541)';
			} else if ($custcode === "C-2536") {
				$projectId = 8;
				$_custcode = "C-2536";
				$root = 'files/camso_acc/C-2536/';
				$rootTemp = 'temp/camso_acc/C-2536/';
				$_subjectMailCust = 'CAMSO TRADING (Private) LIMITED';
			}else if ($custcode === "ISF"){
				$projectId = 9;
				$_custcode = "C-2536";
				$root = 'files/camso_acc/ISF/';
				$rootTemp = 'temp/camso_acc/ISF/';
				$_subjectMailCust = 'CAMSO TRADING (Private) LIMITED';
			}

			$files = $this->automail->getDirRoot($root);

			if (count($files)===0) {
				echo "The file does not exist";
				exit();
			}
			
			foreach ($files as $f) {
				
				// if ($this->camso->checkFileExist($f,$projectId)===true) {
				
				// 	$this->automail->initFolder($rootTemp, 'exists');
				// 	$this->automail->moveFile($root, $rootTemp, 'exists/', $f);

				// }else{

					$dataFromFileName = $this->camso->getDataFromInvoice($f);
					$quantationCamso  = $this->camso->getCamsoFullQuantation($f); 

					// echo "<pre>".print_r($quantationCamso,true)."</pre>";

					if ($custcode !== 'C-1441') 
					{
						$dataFromFileName['DSG_SHIPPINGMARK'] = '';
					}

					if ($custcode === 'C-2536') 
					{
						$_shippingMark = $this->camso->newLineShippingMark($dataFromFileName['DSG_SHIPPINGMARK']);
					}
					else
					{
						$_shippingMark = $dataFromFileName['DSG_SHIPPINGMARK'];
					}

					$state = $this->camso->getStateAndPort($this->camso->getQaNoSharp($quantationCamso), $_custcode);
					// echo $state[0]['DSG_STATE'];
					// echo "\n";
					// echo $state[0]['Port'];
					// exit();
					if (count($state) !== 0) {
						if (count($dataFromFileName) !== 0 && $this->camso->getCustomerCode($f) === $_custcode) {
							
							$mailCustomer = $this->camso->getMailCustomer($projectId,$state[0]['DSG_STATE'],$state[0]['Port']);
							// echo "<pre>".print_r($state,true)."</pre>";
							// echo "<pre>".print_r($mailCustomer,true)."</pre>";

							$mailData[] = [
								'file' => [$root.$f],
								'filename' => [$f],
								'quantation' => $quantationCamso,
								'mail_to' => $mailCustomer['to'],
								'mail_cc' => $mailCustomer['cc'],
								'mail_to_internal' => $mailCustomer['internal'],
								'mail_sender' => $mailCustomer['sender'],
								'data' => [
									'invoice' => strtoupper($dataFromFileName['DATAAREAID']) . '/' . $dataFromFileName['DSG_VOUCHERSERIES'] . '/' . $dataFromFileName['DSG_VOUCHERNO'],
									'lc_no' => $dataFromFileName['DSG_LC_NO'],
									'issue_bank' => $dataFromFileName['DSG_ISSUEDBANK'],
									'shipping_mark' => $_shippingMark,
									'cust_ref' => $dataFromFileName['CUSTOMERREF'],
									'cust_name' => $_subjectMailCust,
									'issue_date' => $dataFromFileName['DSG_LC_DATE1'],
									'country' => $state[0]['Country'],
									'state' => $state[0]['DSG_STATE'],
									'port' => $state[0]['Port']
								]
							];

							// echo "<pre>".print_r($mailData,true)."</pre>";
						}else{
							$stackCamsoLoadstar_FAIL[] = $f;
						}
					}

				// }
			}
			// echo "<pre>".print_r($mailData,true)."</pre>";
			// exit();
			
			if (count($mailData) !== 0) {
				
				foreach ($mailData as $value) {


						$checkDocsInFileName = $this->camso->isDocsInFileName($value['filename']);

						if (count($checkDocsInFileName) !== 0) {
							// send to acc
							$sendEmailAcc = $this->email->sendEmail(
								$this->camso->getCamsoSubject($value['quantation'], $value['data']['cust_name'], $custcode),
								$this->camso->getCamsoBody(
									$value['data']['cust_ref'], 
									$value['quantation'], 
									$value['data']["invoice"], 
									$value['data']["lc_no"], 
									str_replace('\n', '<br>', $value['data']["shipping_mark"]), 
									$value['data']["issue_bank"],
									$value['data']["issue_date"],
									$value['data']['cust_name'],
									$custcode
								),
								$acc_fin['to_acc'], 
								// $acc_fin_dev['to'],
								[], 
								[], 
								[$root . $value['filename'][0]],
								$value['mail_sender'][0],
								$value['mail_sender'][0]
							);
							
							if($sendEmailAcc === true) {
								echo "Message has been sent Acc\n";
							}else{
								echo $sendEmailAcc;
							}
						}

						// sendSucess movefile
						$this->automail->initFolder($rootTemp, 'logs');
						$this->automail->moveFile($root, $rootTemp, 'logs/', $value['filename'][0]);
					
				}
				
			}

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function weeklyCamso($request, $response, $args)
    {
        try {

			$projectId = 45;
			$root = 'D:\automail\shipping_document\camso\\';
			$rootTemp = 'D:\automail\shipping_document\camso\temp\\';
			
			$files = $this->automail->getDirRoot($root);
			$getMail = $this->camso->getMailCustomerWeekly($projectId);

			if (count($files)===0) 
			{
				echo "The file does not exist";
				exit();
			}
			
			foreach ($files as $file) 
			{
				if (gettype($file) !== 'array') 
				{
                    if ($file !== 'Thumbs.db') {
						//AOT_Booking_Daily_Report
                        $allFiles[] = [
                            'file_name' => $file
                        ];
                    }
                }
            }
			// echo "<pre>".print_r($allFiles,true)."</pre>";
   //          exit;
			foreach ($allFiles as $a) 
			{
				if ($a['file_name'] === "Shipping_document_camso_weekly.xls") 
				{
					// echo "<pre>".print_r($getMail,true)."</pre>";
					// exit;
					$subject = "Report : Shipping document_Camso Trading (Private) Limited & Camso Loadstar (Private) Limited";
					$body = "Please see updated shipping document report as attached file for your reference.  Thank you."."<br><br>"."Best reqard,"."<br>"."Khanittha(Sai)";

					$sendEmail = $this->email->sendEmail(
						$subject,
						$body,
						$getMail['to'],
						$getMail['cc'],
						[],
						[$root . $a['file_name']],
						$getMail['sender'][0],
						$getMail['sender'][0]
					);
				
					if ($sendEmail == true) 
					{
						echo "Message has been sent !!\n";
						$sendEmailInternal = $this->email->sendEmail(
							$subject,
							$body,
							$getMail['internal'],
							[],
							[],
							[$root . $a['file_name']],
							$getMail['sender'][0],
							$getMail['sender'][0]
						);


						$logging = $this->automail->logging(
							$projectId,
							'Message has been sent',
							null,
							null,
							null,
							null,
							null,
							$files,
							'File'
						);

						$this->automail->loggingEmail($logging,$getMail['to'],1); //1To
						$this->automail->loggingEmail($logging,$getMail['cc'],2); //cc

						if ($sendEmailInternal == true) {
							$this->automail->loggingEmail($logging,$getMail['internal'],1);
						}
						$this->automail->initFolder($rootTemp, 'logs');
						$this->automail->moveFile($root, $rootTemp, 'logs/', $a['file_name']);	
					}
					else
					{
						echo $sendEmail;
						// sendfailed movefile
						$this->automail->initFolder($root, 'failed');
						$this->automail->moveFile($root, $root, 'failed/',$a['file_name']);
					}
				}
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

}