<?php

namespace App\Api;

use App\Common\View;
use App\Api\ApiAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Shipping\ShippingAPI;

class ApiController {

	public function __construct() {
		$this->view = new View;
		$this->api = new ApiAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
		$this->shipping = new ShippingAPI;
	}

	public function booking($request, $response, $args) {
			try {
					$projectId = 37;
          $root = 'files/api/booking/';
          $rootTemp = 'temp/api/booking/';
					$fileOkay = [];
				 	$SOSend = "";
					$files = $this->automail->getDirRoot($root);
					$getMail = $this->api->getMailCustomer($projectId);

					if (count($files)===0) {
						echo "The file does not exist";
						exit();
	        }
					foreach ($files as $f) {
						 $so = $this->api->getSOFromFileBooking($f);
						 foreach ($so as $key => $value) {
							$SOSend .= "'".$value."'".",";
							$dataso = substr($SOSend, 0, -1);
					   }
					 	  $SOSend ="";
							if ($this->api->isBookingReviseinternal($f) === true) {
								 $dataGet = $this->api->isBookingTFileMatchAx($dataso);
								 $SOS = $dataGet["SO"];
								 $PO = $dataGet["PO"];
								 $PI = $dataGet["PI"];
								 $CY = $dataGet["CY"];
								 $RTN = $dataGet["RTN"];
								 $SalName = $dataGet["SalName"];
								 $Numbook = $dataGet["Numbook"];
								 $Loadingdate = $dataGet["Loadingdate"];
								 $SubHC = $dataGet["HC"];
								 $Booking_detail = $dataGet["Booking_detail"];
								 $AGENT = $dataGet["AGENT"];
								 $Type = "Revice";
								 if ($SOS !== "" ) {
									 $fileOkay[] = [
										 'file' => $f,
										 'SO' => array_unique($SOS),
										 'PO' => array_unique($PO),
										 'PI' => array_unique($PI),
										 'CY' => array_unique($CY),
										 'RTN' => array_unique($RTN),
										 'SalName' =>  array_unique($SalName),
										 'Numbook' => array_unique($Numbook),
										 'RTN' => array_unique($RTN),
										 'Loadingdate' => array_unique($Loadingdate),
										 'Type' => $Type,
										 'SubHC' => array_unique($SubHC),
										 'Booking_detail' => array_unique($Booking_detail),
										 'AGENT' => array_unique($AGENT)
									 ];
								 } else {
									 $fileFailed[] = $f;
								 }
							}else{

								 	$dataGet = $this->api->isBookingTFileMatchAx($dataso);
								 	$SOS = $dataGet["SO"];
									$PO = $dataGet["PO"];
									$PI = $dataGet["PI"];
									$CY = $dataGet["CY"];
									$RTN = $dataGet["RTN"];
									$SalName = $dataGet["SalName"];
									$Numbook = $dataGet["Numbook"];
									$Loadingdate = $dataGet["Loadingdate"];
									$SubHC = $dataGet["HC"];
									$Booking_detail = $dataGet["Booking_detail"];
									$AGENT = $dataGet["AGENT"];
									$Type = "New";
									if ($SOS !== "" ) {
										$fileOkay[] = [
											'file' => $f,
											'SO' => array_unique($SOS),
											'PO' => array_unique($PO),
											'PI' => array_unique($PI),
											'CY' => array_unique($CY),
											'RTN' => array_unique($RTN),
											'SalName' =>  array_unique($SalName),
											'Numbook' => array_unique($Numbook),
											'RTN' => array_unique($RTN),
											'Loadingdate' => array_unique($Loadingdate),
											'Type' => $Type,
											'SubHC' => array_unique($SubHC),
											'Booking_detail' => array_unique($Booking_detail),
											'AGENT' => array_unique($AGENT)
										];
									} else {
						 				$fileFailed[] = $f;
						 			}
								}

							}
					// echo "<pre>".print_r($fileOkay,true)."</pre>";
					// exit;
					if (count($fileOkay) !== 0) {
						foreach ($fileOkay as $data) {
							$goodData[] = [
								'file' => $data['file'],
								//'sender' => 'kanokporn_s@deestone.com',
								'sender' => 'kanokporn_s@deestone.com',
								//$automail->getEmailFromCustomerCode($data['customer']),
								'internal' => 'kanokporn_s@deestone.com',
					 			'name' => $data['SalName'],
					 			'SO' => $data['SO'],
					 			'PO' => $data['PO'],
					 			'Type' => $data['Type'],
					 			'Numbook' => $data["Numbook"],
								'PI' => $data["PI"],
								'Loadingdate' => $data["Loadingdate"],
								'RTN' => $data["RTN"],
								'CY' => $data["CY"],
								'SubHC' => $data["SubHC"],
								'Booking_detail' => $data["Booking_detail"],
								'AGENT' => $data["AGENT"]
							];
						}
						$txtSo = '';
						$txtPo = '';
						$txtPI = '';
						$txtLd = '';
						$txtCy = '';
						$txtRtn = '';
						$txtHc = '';
						$txtBk = '';
						$txtsub = '';
						$subject = '';
						foreach($goodData as $m) {
							if (count($m['SO'])>1) {
								for ($i=0; $i < count($m['SO']); $i++) {
									$txtSo .= $m['SO'][$i].",";
								}
								$txtSo = substr($txtSo,0,-1);
							}else{
								$txtSo .= $m['SO'][0];
							}
							if (count($m['PO'])>1) {
								for ($i=0; $i < count($m['PO']); $i++) {
									$txtPo .= $m['PO'][$i].",";
								}
								$txtPo = substr($txtPo,0,-1);
							}else{
								$txtPo .= $m['PO'][0];
							}
							if (count($m['PI'])>1) {
								for ($i=0; $i < count($m['PI']); $i++) {
									$txtPI .= $m['PI'][$i].",";
								}
								$txtPI = substr($txtPI,0,-1);
							}else{
								$txtPI .= $m['PI'][0];
							}
							if (count($m['Loadingdate'])>1) {
								for ($i=0; $i < count($m['Loadingdate']); $i++) {
									$txtLd .= $m['Loadingdate'][$i].",";
								}
								$txtLd = substr($txtLd,0,-1);
							}else{
								$txtLd .= $m['Loadingdate'][0];
							}
							if (count($m['CY'])>1) {
								for ($i=0; $i < count($m['CY']); $i++) {
									$txtCy .= $m['CY'][$i].",";
								}
								$txtCy = substr($txtCy,0,-1);
							}else{
								$txtCy .= $m['CY'][0];
							}
							if (count($m['RTN'])>1) {
								for ($i=0; $i < count($m['RTN']); $i++) {
									$txtRtn .= $m['RTN'][$i].",";
								}
								$txtRtn = substr($txtRtn,0,-1);
							}else{
								$txtRtn .= $m['RTN'][0];
							}
							if (count($m['SubHC'])>1) {
								for ($i=0; $i < count($m['SubHC']); $i++) {
									$txtHc .= $m['SubHC'][$i].",";
								}
									$txtHc = substr($txtHc,0,-1);
							}else{
								$txtHc .= $m['SubHC'][0];
							}
							if (count($m['Booking_detail'])>1) {
								for ($i=0; $i < count($m['Booking_detail']); $i++) {
									if($m['Booking_detail'][$i] == NULL){
										$txtBk .= $m['Booking_detail'][0];
									}else {
										$txtBk .= $m['Booking_detail'][$i].",";
									}

								}
								$txtBk = substr($txtBk,0,-1);
							}else{

								$txtBk .= $m['Booking_detail'][0];
							}
							$body = $this->api->getBookingBody_v3($txtSo, $txtPo, $txtPI, $txtLd, $txtCy, $txtRtn, $txtHc, $txtBk, $m['AGENT']);
							$subject = $this->api->getBookingSubject_internalAPI($txtSo, $m['name'], $txtPI, $m['Type'], $m['Numbook']);
							// echo "<pre>".print_r($getMail,true)."</pre>";
							// exit;
							$sendEmailInternal = $this->email->sendEmail(
								$subject,
								$body,
								$getMail['to'],
								$getMail['cc'],
								[],
								[$root . $m['file']],
								$getMail['sender'][0],
								$getMail['sender'][0]
							);
							if ($sendEmailInternal == true) {
								$logging = $this->automail->logging(
										$projectId,
										'Message has been sent',
										null,
										$txtSo,
										null,
										null,
										null,
										$m['file'],
										'File'
								);

								$this->automail->loggingEmail($logging,$getMail['to'],1);
								$this->automail->loggingEmail($logging,$getMail['cc'],2);
								echo "Message has been sent internal\n";
								$this->automail->initFolder($rootTemp, 'logs');
							 	$this->automail->moveFile($root, $rootTemp, 'logs/', $m['file']);
							}
							else{
								echo $sendEmailInternal;
								// sendfailed movefile
								$this->automail->initFolder($root, 'failed');
								$this->automail->moveFile($root, $root, 'failed/', $m['file']);
							}
							// echo "<pre>";
							//  print_r($getMail);
							//  echo "</pre>";

							$txtSo = '';
							$txtPo = '' ;
							$txtPI = '' ;
							$txtLd = '' ;
							$txtCy = '' ;
							$txtRtn = '' ;
							$txtHc = '' ;
							$txtBk = '' ;
							$txtsub = '';



					 }



				 }

			} catch (\Exception $e) {
				echo $e->getMessage();
			}
    }

	public function shipping_api_cif($request, $response, $args) {
		try {

			$projectId = 42;
			$root = 'files/api_shipping/insp_cif/';
			$rootTemp = 'temp/api_shipping/insp_cif/';
			$filesOkay = [];
			$fileFailed = [];

			$files = $this->automail->getDirRoot($root);
			$email_failed = $this->shipping->getMailCustomer($projectId);
			
			foreach ($files as $file) {

				if ($this->shipping->isFormatile($file) === true) {

					$customerCode = $this->automail->getCustomerCode($file);
					$quantation = $this->automail->getQuantationArray($file);
					$qaConverted = $this->automail->convertArrayToInSQL($quantation);
					$invoice = $this->automail->getInvoice($file);
					$invoiceNumber = substr($invoice,3,8);
					
					if ($this->shipping->mapQuantationManyQA($customerCode, $qaConverted, $invoiceNumber) === true && $this->shipping->isAPI($file) === true && $this->shipping->MapAgent($customerCode, $qaConverted, $invoiceNumber, 'CIF') === true) {
						$filesOkay[] = [
							'customer' => $customerCode,
							'file' => $file
						];
					} else {
						$fileFailed[] = $file;
					} 

				}else{
					$fileFailed[] = $file;
				}
				
			}
			
			// echo "<pre>".print_r($filesOkay,true)."</pre>";
			// echo "<hr>";
			// echo "<pre>".print_r($fileFailed,true)."</pre>";
			// exit;
			
			if (count($filesOkay) > 0) {
				foreach ($filesOkay as $f) {
					$email = $this->automail->getCustomerMail($f['customer']);
					$internal = $this->automail->getEmailFromCustomerCode($f['customer']);
					$acc_fin = ['to' => ['shippingdoc@deestone.com'], 'cc' => []];
					$acc_surrender = $this->shipping->getMailCustomer($projectId);

					// $email = ['to' => ['sakunee_b@deestone.com','armena_c@deestone.com','weerawat_y@deestone.com'], 'cc' => []];
					// $internal = ['to' => ['doc.ds@deestone.com','doc.dsc@deestone.com','nutcha_c@deestone.com','witchuta_v@deestone.com'], 'cc' => []];
					// $acc_fin = ['to' => ['weerawat_y@deestone.com'], 'cc' => []];
					// $acc_surrender = ['internalcc' => ['weerawat_y@deestone.com']];

					$success[] = [
						'customer' => $f['customer'],
						'email' => $email,
						'file' => $f['file'],
						'sender' => $internal,
						'internal' => $internal,
						'acc_fin' => $acc_fin,
						'acc_surrender' => $acc_surrender['internalcc']
					];
				}
				// echo "<pre>".print_r($success,true)."</pre>";
				// exit;
				
				foreach ($success as $m) {
					if( $this->shipping->getShippingBody($m['file']) !== false ) {
						
						// echo $this->shipping->getShippingSubject($m['file']);
						// echo "<br>"; 
						// echo $this->shipping->getShippingBody($m['file']); 
						// echo "<br>";
						// var_dump($email['to']);
						// echo "<br>";
						// echo $m['file'];
						// echo "<hr>";
						$file = [];
						$file[] = $root.$m['file'];
						$sendEmail = $this->email->sendEmail(
							$this->shipping->getShippingSubject($m['file']), 
							$this->shipping->getShippingBody($m['file']), 
							// $email['to'], 
							$m['email']['to'],
							[], 
							[], 
							$file,
							$m['sender'],
							$m['sender']
						);

						if($sendEmail == true) {
							echo "Message has been sent External : ". $m['file'] ."<br>";
							
							$sendEmailInternal = $this->email->sendEmail(
								$this->shipping->getShippingSubject($m['file']), 
								$this->shipping->getShippingBody($m['file']), 
								// $internal['to'], 
								[$m['internal']],
								[], 
								[], 
								$file,
								$m['sender'],
								$m['sender']
							);
							
							$this->automail->logging(
								$projectId,
								'Message has been sent',
								$m['customer'],
								null,
								null,
								null,
								null,
								$m['file'],
								'file'
							);

							$checkDocsInFileName = $this->shipping->isDocsInFileName($m['file']);

							if ( $checkDocsInFileName ===  true ) {
								// send to account
								$this->email->sendEmail(
									$this->shipping->getShippingSubject($m['file']), 
									$this->shipping->getShippingBody($m['file']), 
									// $acc_fin['to'], 
									$m['acc_fin']['to'],
									[], 
									[], 
									$file,
									$m['sender'],
									$m['sender']
								);
							}

							if ($this->shipping->isSurrender($m['file']) === true) {
								// send to account surrender
								$this->email->sendEmail(
									$this->shipping->getShippingSubject($m['file']), 
									$this->shipping->getShippingBody($m['file']), 
									// $acc_surrender['internalcc'], 
									$m['acc_surrender'],
									[], 
									[], 
									$file,
									$m['sender'],
									$m['sender']
								);
							}
							// MOVE FILE
							$this->automail->initFolder($rootTemp, 'temp');
                        	$this->automail->moveFile($root, $rootTemp, 'temp/', $m['file']);
						}

					}
				}
			}
			
			// exit;
			if (count($fileFailed) > 0) {
				$email_failed = $this->shipping->getMailCustomer($projectId);
				$files_Outcorrect = $this->shipping->pathTofile($fileFailed,$root);

                $sendEmailFailedOut = $this->email->sendEmail(
                    $this->automail->getSubjectReportFailed(), 
                    $this->automail->getBodyReportFailed($fileFailed, 'Shipping document & inspection API CIF', 'ไฟล์ไม่ถูกต้อง'), 
                    $this->shipping->getMailFailed(),
                    [],
                    [],
                    $files_Outcorrect,
                    $email_failed['sender'][0],
                    $email_failed['sender'][0]
                );

                if($sendEmailFailedOut == true) {
                    // sendfailed movefile
                    foreach ($fileFailed as $file) {
                        $this->automail->initFolder($rootTemp, 'failed');
						$this->automail->moveFile($root, $rootTemp, 'failed/', $file);
                    }
                }
			}

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function shipping_api_cds($request, $response, $args) {
		try {

			$projectId = 42;
			$root = 'files/api_shipping/insp_cds/';
			$rootTemp = 'temp/api_shipping/insp_cds/';
			$filesOkay = [];
			$fileFailed = [];

			$files = $this->automail->getDirRoot($root);
			$email_failed = $this->shipping->getMailCustomer($projectId);
			
			foreach ($files as $file) {

				if ($this->shipping->isFormatile($file) === true) {

					$customerCode = $this->automail->getCustomerCode($file);
					$quantation = $this->automail->getQuantationArray($file);
					$qaConverted = $this->automail->convertArrayToInSQL($quantation);
					$invoice = $this->automail->getInvoice($file);
					$invoiceNumber = substr($invoice,3,8);
					
					if ($this->shipping->mapQuantationManyQA($customerCode, $qaConverted, $invoiceNumber) === true && $this->shipping->isAPI($file) === true && $this->shipping->MapAgent($customerCode, $qaConverted, $invoiceNumber, 'CDS') === true) {
						$filesOkay[] = [
							'customer' => $customerCode,
							'file' => $file
						];
					} else {
						$fileFailed[] = $file;
					} 

				}else{
					$fileFailed[] = $file;
				}

			}
			
			// echo "<pre>".print_r($filesOkay,true)."</pre>";
			// echo "<hr>";
			// echo "<pre>".print_r($fileFailed,true)."</pre>";
			// exit;
			
			if (count($filesOkay) > 0) {
				foreach ($filesOkay as $f) {
					// $email = $this->automail->getCustomerMail($f['customer']);
					// $internal = $this->automail->getEmailFromCustomerCode($f['customer']);
					// $acc_fin = ['to' => ['shippingdoc@deestone.com'], 'cc' => []];
					$email = $this->shipping->getMailCustomer($projectId);

					// $email = ['to' => ['weerawat_y@deestone.com','harit_j@deestone.com'], 'cc' => []];
					// $internal = ['to' => ['harit_j@deestone.com'], 'cc' => []];
					// $acc_fin = ['to' => ['harit_j@deestone.com','wandee_h@deestone.com'], 'cc' => []];

					$success[] = [
						'customer' => $f['customer'],
						'external' => $email['to'],
						'file' => $f['file'],
						'sender' => $email['sender'],
						'internal' => $email['internal'],
						'acc_fin' => $email['internalcc']
					];
				}
				// echo "<pre>".print_r($success,true)."</pre>";
				// exit;
				
				foreach ($success as $m) {
					if( $this->shipping->getShippingBody($m['file']) !== false ) {
						
						// echo $this->shipping->getShippingSubject($m['file']);
						// echo "<br>"; 
						// echo $this->shipping->getShippingBody($m['file']); 
						// echo "<br>";
						// var_dump($email['to']);
						// echo "<br>";
						// echo $m['file'];
						// echo "<hr>";
						$file = [];
						$file[] = $root.$m['file'];
						$sendEmail = $this->email->sendEmail(
							$this->shipping->getShippingSubject($m['file']), 
							$this->shipping->getShippingBody($m['file']), 
							$m['external'], 
							[], 
							[], 
							$file,
							$m['sender'][0],
							$m['sender'][0]
						);

						if($sendEmail == true) {
							echo "Message has been sent External : ". $m['file'] ."<br>";
							
							$sendEmailInternal = $this->email->sendEmail(
								$this->shipping->getShippingSubject($m['file']), 
								$this->shipping->getShippingBody($m['file']), 
								$m['internal'], 
								[], 
								[], 
								$file,
								$m['sender'][0],
								$m['sender'][0]
							);
							
							$this->automail->logging(
								$projectId,
								'Message has been sent',
								$m['customer'],
								null,
								null,
								null,
								null,
								$m['file'],
								'file'
							);

							$checkDocsInFileName = $this->shipping->isDocsInFileName($m['file']);

							if ( $checkDocsInFileName ===  true ) {
								// send to account
								$this->email->sendEmail(
									$this->shipping->getShippingSubject($m['file']), 
									$this->shipping->getShippingBody($m['file']), 
									$m['acc_fin'], 
									[], 
									[], 
									$file,
									$m['sender'][0],
									$m['sender'][0]
								);
							}

							// MOVE FILE
							$this->automail->initFolder($rootTemp, 'temp');
                        	$this->automail->moveFile($root, $rootTemp, 'temp/', $m['file']);
						}

					}
				}
			}
			
			// exit;
			if (count($fileFailed) > 0) {
				$email_failed = $this->shipping->getMailCustomer($projectId);
				$files_Outcorrect = $this->shipping->pathTofile($fileFailed,$root);

                $sendEmailFailedOut = $this->email->sendEmail(
                    $this->automail->getSubjectReportFailed(), 
                    $this->automail->getBodyReportFailed($fileFailed, 'Shipping document & inspection API CDS', 'ไฟล์ไม่ถูกต้อง'), 
                    $email_failed['failed'],
                    // $email_failed['to'],
                    [],
                    [],
                    $files_Outcorrect,
                    $email_failed['sender'][0],
                    $email_failed['sender'][0]
                );

                if($sendEmailFailedOut == true) {
                    // sendfailed movefile
                    foreach ($fileFailed as $file) {
                        $this->automail->initFolder($rootTemp, 'failed');
                        $this->automail->moveFile($root, $rootTemp, 'failed/', $file);
                    }
                }
			}

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function shipping_api_doc($request, $response, $args) {
		try {

			$projectId = 42;
			$root = 'files/api_shipping/doc/';
			$rootTemp = 'temp/api_shipping/doc/';
			$filesOkay = [];
			$fileFailed = [];

			$files = $this->automail->getDirRoot($root);
			$email_failed = $this->shipping->getMailCustomer($projectId);
			
			foreach ($files as $file) {

				if ($this->shipping->isFormatile($file) === true) {
					
					$customerCode = $this->automail->getCustomerCode($file);
					$quantation = $this->automail->getQuantationArray($file);
					$qaConverted = $this->automail->convertArrayToInSQL($quantation);
					$invoice = $this->automail->getInvoice($file);
					$invoiceNumber = substr($invoice,3,8);
					
					if ($this->shipping->mapQuantationManyQA($customerCode, $qaConverted, $invoiceNumber) === true && $this->shipping->isAPI($file) === true) {
						$filesOkay[] = [
							'customer' => $customerCode,
							'file' => $file
						];
					} else {
						$fileFailed[] = $file;
					} 

				}else{
					$fileFailed[] = $file;
				}

			}
			
			// echo "<pre>".print_r($filesOkay,true)."</pre>";
			// echo "<hr>";
			// echo "<pre>".print_r($fileFailed,true)."</pre>";
			// exit;
			
			if (count($filesOkay) > 0) {
				foreach ($filesOkay as $f) {
					$email = $this->automail->getCustomerMail($f['customer']);
					$internal = $this->automail->getEmailFromCustomerCode($f['customer']);
					$acc_fin = ['to' => ['shippingdoc@deestone.com'], 'cc' => []];
					$acc_surrender = $this->shipping->getMailCustomer($projectId);

					// $email = ['to' => ['sakunee_b@deestone.com','armena_c@deestone.com','weerawat_y@deestone.com'], 'cc' => []];
					// $internal = ['to' => ['doc.ds@deestone.com','doc.dsc@deestone.com','nutcha_c@deestone.com','witchuta_v@deestone.com'], 'cc' => []];
					// $acc_fin = ['to' => ['weerawat_y@deestone.com'], 'cc' => []];
					// $acc_surrender = ['internalcc' => ['weerawat_y@deestone.com']];

					$success[] = [
						'customer' => $f['customer'],
						'email' => $email,
						'file' => $f['file'],
						'sender' => $internal,
						'internal' => $internal,
						'acc_fin' => $acc_fin,
						'acc_surrender' => $acc_surrender['internalcc']
					];
				}
				// echo "<pre>".print_r($success,true)."</pre>";
				// exit;
				
				foreach ($success as $m) {
					if( $this->shipping->getShippingBody($m['file']) !== false ) {
						
						// echo $this->shipping->getShippingSubject($m['file']);
						// echo "<br>"; 
						// echo $this->shipping->getShippingBody($m['file']); 
						// echo "<br>";
						// var_dump($email['to']);
						// echo "<br>";
						// echo $m['file'];
						// echo "<hr>";
						$file = [];
						$file[] = $root.$m['file'];
						$sendEmail = $this->email->sendEmail(
							$this->shipping->getShippingSubject($m['file']), 
							$this->shipping->getShippingBody($m['file']), 
							// $email['to'],
							$m['email']['to'], 
							[], 
							[], 
							$file,
							$m['sender'],
							$m['sender']
						);

						if($sendEmail == true) {
							echo "Message has been sent External : ". $m['file'] ."<br>";
							
							$sendEmailInternal = $this->email->sendEmail(
								$this->shipping->getShippingSubject($m['file']), 
								$this->shipping->getShippingBody($m['file']), 
								// $internal['to'],
								[$m['internal']], 
								[], 
								[], 
								$file,
								$m['sender'],
								$m['sender']
							);
							
							$this->automail->logging(
								$projectId,
								'Message has been sent',
								$m['customer'],
								null,
								null,
								null,
								null,
								$m['file'],
								'file'
							);

							$checkDocsInFileName = $this->shipping->isDocsInFileName($m['file']);

							if ( $checkDocsInFileName ===  true ) {
								// send to account
								$this->email->sendEmail(
									$this->shipping->getShippingSubject($m['file']), 
									$this->shipping->getShippingBody($m['file']), 
									// $acc_fin['to'],
									$m['acc_fin']['to'], 
									[], 
									[], 
									$file,
									$m['sender'],
									$m['sender']
								);
							}

							if ($this->shipping->isSurrender($m['file']) === true) {
								// send to account surrender
								$this->email->sendEmail(
									$this->shipping->getShippingSubject($m['file']), 
									$this->shipping->getShippingBody($m['file']), 
									// $acc_surrender['internalcc'],
									$m['acc_surrender'],  
									[], 
									[], 
									$file,
									$m['sender'],
									$m['sender']
								);
							}
							// MOVE FILE
							$this->automail->initFolder($rootTemp, 'temp');
                        	$this->automail->moveFile($root, $rootTemp, 'temp/', $m['file']);
						}

					}
				}
			}
			
			// exit;
			if (count($fileFailed) > 0) {
				$email_failed = $this->shipping->getMailCustomer($projectId);
				$files_Outcorrect = $this->shipping->pathTofile($fileFailed,$root);

                $sendEmailFailedOut = $this->email->sendEmail(
                    $this->automail->getSubjectReportFailed(), 
                    $this->automail->getBodyReportFailed($fileFailed, 'Shipping document & inspection API DOC', 'ไฟล์ไม่ถูกต้อง'), 
                    $this->shipping->getMailFailed(),
                    [],
                    [],
                    $files_Outcorrect,
                    $email_failed['sender'][0],
                    $email_failed['sender'][0]
                );

                if($sendEmailFailedOut == true) {
                    // sendfailed movefile
                    foreach ($fileFailed as $file) {
                        $this->automail->initFolder($rootTemp, 'failed');
						$this->automail->moveFile($root, $rootTemp, 'failed/', $file);
                    }
                }
			}

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}
	
	public function cds_vgm($request, $response, $args) 
	{
		try 
		{
			$projectId = 54;
            $root = 'files/api_shipping/cds_vgm/';
            $rootTemp = 'temp/api_shipping/cds_vgm/';
            $getEMail = $this->api->getEmailList($projectId);
			$files = $this->automail->getDirRoot($root);

			if (count($files)===0) {
				echo "The file does not exist";
				exit();
            }

			foreach ($files as $file) {
                if (gettype($file) !== 'array') {
                    if ($file !== 'Thumbs.db') {
						preg_match('/C2720/', $file, $customer);
                        preg_match('/QA(a?.........)/i', $file, $output_qa);
						preg_match('/INV(.+\d)/', $file, $output_inv);
						preg_match('/PO#(.+\d)\b/', $file, $po1);
						preg_match('/VGM|INV[^0-9]PL|INSP/', $file, $output_type);

						preg_match('/PO#(.+)-/', $po1[0], $output_po);

						if( isset($output_type[0])) {
							$type = $output_type[0];
						}
						else{
							$type = "Failed";
							
						}

                        $allFiles[] = [
                            'file_name' => $file,
							'customer' => $customer[0],
                            'pi_id' => $output_qa[0],
							'inv' => $output_inv[1],
							'po_text' => substr($output_po[0], 0, -1),
							'po_no' => $output_po[1],
                            'file_type' => $type
                        ];
                    }
                }
            }
            sort($allFiles);

			// echo "<pre>".print_r($allFiles,true)."</pre>";
			// exit;
            
			$fileOutCombineFormat = [];
            $fileOutcorrectFormat = [];
            $fileIncorrectFormat = [];
            $tmp_file = [];
            $count_file = 0;
            $counter_tmp_file = 0;
            $pi_id = '';
            $formatType = ['VGM','INV+PL','INSP'];
			$customerCheck = 'C2720';


			foreach ($allFiles as $value) 
			{
				if (!in_array($value['file_type'],$formatType)) 
				{
					
                    $fileOutcorrectFormat[] = $value['file_name'];
                }
				else
				{
                    if ($this->api->isAPIFileMatchAx($value['pi_id'],$value['inv']) === true) 
					{

                            if ($value['pi_id'] == $pi_id) 
							{
                                $tmp_file[$counter_tmp_file][] = $value['file_name'];
                            }else
							{
                                $count_file = 0;
                                $counter_tmp_file++;
                                $tmp_file[$counter_tmp_file][] = $value['file_name'];
                            }

                    } 
					else 
					{
                        $fileIncorrectFormat[] = $value['file_name'];
                    }

                    $pi_id = $value['pi_id'];
                }
            }

			// echo "<pre>".print_r($tmp_file,true)."</pre>";
            // echo "<pre>".print_r($fileIncorrectFormat,true)."</pre>";
            // echo "<pre>".print_r($fileOutcorrectFormat,true)."</pre>";
            // echo "<pre>".print_r($getEMail,true)."</pre>";
			// exit;

			for ($i=1; $i <= count($tmp_file); $i++) 
			{
				if (count($tmp_file[$i]) === 0) {
                    exit('Folder is empty.' . PHP_EOL);
                }
				else if (count($tmp_file[$i]) === 3) 
				{
					$subject = $this->api->getSubject_CDS($tmp_file[$i][0],'CDS');
                    $body = $this->api->getBody_CDS($tmp_file[$i][0]);
                    $files = $this->api->pathTofile($tmp_file[$i],$root);

					// echo "<pre>".print_r($subject,true)."</pre>";
					// echo "<pre>".print_r($body,true)."</pre>";
					// echo "<pre>".print_r($files,true)."</pre>";
					
                    // send External
                    $sendEmail = $this->email->sendEmail(
                        $subject,
                        $body,
                        $getEMail['toEX'],
                        $getEMail['ccEX'],
                        [],
                        $files,
                        $getEMail['sender'],
                        $getEMail['sender']
                    );

                    if($sendEmail == true) {
                        echo "Message has been sent External\n";

                        $sendEmailInternal = $this->email->sendEmail(
                            $subject,
                            $body,
                            $getEMail['toIN'],
                            [],
                            [],
                            $files,
                            $getEMail['sender'],
                            $getEMail['sender']
                        );

                        $fileslogs = implode(" & ",$tmp_file[$i]);
                        // insert logs
                        $logging = $this->automail->logging(
                            $projectId,
                            'Message has been sent',
                            null,
                            null,
                            null,
                            null,
                            null,
                            $fileslogs,
                            'File'
                        );

                        $this->automail->loggingEmail($logging,$getEMail['toEX'],1);
                        $this->automail->loggingEmail($logging,$getEMail['ccEX'],2);
						
                        if ($sendEmailInternal == true) 
						{
                            $this->automail->loggingEmail($logging,$getEMail['toIN'],1);
							echo "Message has been sent Internal\n";
                        }

                        foreach ($tmp_file[$i] as $file) {
                            // sendSucess movefile
                            $this->automail->initFolder($rootTemp, 'temp');
                            $this->automail->moveFile($root, $rootTemp, 'temp/', $file);
                        }

                    }

				}
				else{
					foreach ($tmp_file[$i] as $k => $v) 
					{
                        $fileOutCombineFormat[] = $v;
                    }
				}
            }

			//not found in ax
			if (count($fileIncorrectFormat) > 0) {

                $files_Incorrect = $this->api->pathTofile($fileIncorrectFormat,$root);

                $sendEmailFailed = $this->email->sendEmail(
                    $this->api->getSubject_CDS($fileIncorrectFormat,'ERROR'),
                    $this->api->getBodyCDS_Failed($fileIncorrectFormat,'Fail not found QA or INV in ax please check.'),
                    $getEMail['toFailed'],
                    [],
                    [],
                    $files_Incorrect,
                    $getEMail['sender'],
                    $getEMail['sender']
                );

                if($sendEmailFailed == true) {
                    // sendfailed movefile
                    foreach ($fileIncorrectFormat as $file) {
                        $this->automail->initFolder($root, 'failed');
                        $this->automail->moveFile($root, $root, 'failed/', $file);
                    }
                }
            }
			
			//not found type _VGM,_INV,_PL&INSP
			if (count($fileOutcorrectFormat) > 0) 
			{
                $files_Outcorrect = $this->api->pathTofile($fileOutcorrectFormat,$root);

                $sendEmailFailedOut = $this->email->sendEmail(
                    $this->api->getSubject_CDS($fileOutcorrectFormat,'ERROR'),
                    $this->api->getBodyCDS_Failed($fileOutcorrectFormat,'Fail check file name (VGM, INV, PL&INSP)'),
					$getEMail['toFailed'],
                    [],
                    [],
                    $files_Outcorrect,
                    $getEMail['sender'],
                    $getEMail['sender']
                );

                if($sendEmailFailedOut == true) {
                    // sendfailed movefile
                    foreach ($fileOutcorrectFormat as $file) {
                        $this->automail->initFolder($root, 'failed');
                        $this->automail->moveFile($root, $root, 'failed/', $file);
                    }
                }
            }

			// count file != 3
			if (count($fileOutCombineFormat) > 0) {
                $files_OutCombine = $this->api->pathTofile($fileOutCombineFormat,$root);

                $sendEmailFailedOutCombine = $this->email->sendEmail(
                    $this->api->getSubject_CDS($fileOutCombineFormat,'ERROR'),
                    $this->api->getBodyCDS_Failed($fileOutCombineFormat,'Fail sending File not Combine'),
                    $getEMail['toFailed'],
                    [],
                    [],
                    $files_OutCombine,
                    $getEMail['sender'],
                    $getEMail['sender']
                );

                if($sendEmailFailedOutCombine == true) {
                    // sendfailed movefile
                    foreach ($fileOutCombineFormat as $file) {
                        $this->automail->initFolder($root, 'failed');
                        $this->automail->moveFile($root, $root, 'failed/', $file);
                    }
                }
            }
		} 
		catch (\Exception $e) 
		{
			echo $e->getMessage();
		}
	}
}
