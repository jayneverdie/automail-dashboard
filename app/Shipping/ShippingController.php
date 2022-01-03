<?php

namespace App\Shipping;

use App\Common\View;
use App\Shipping\ShippingAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;

class ShippingController {

	public function __construct() {
		$this->view = new View;
		$this->shipping = new ShippingAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}

	public function all($request, $response, $args) {
		return $this->view->render('pages/shipping/all');
	}

	public function allSend($request, $response, $args) {
		try {

			$projectId = 5;
			$root = 'files/shipping_doc_all/';
			$rootTemp = 'temp/shipping_doc_all/';
			$filesOkay = [];
			$fileFailed = [];

			$files = $this->automail->getDirRoot($root);

			foreach ($files as $file) {

				if ($this->shipping->isFormatile($file) === true) {

					$customerCode = $this->automail->getCustomerCode($file);
					$quantation = $this->automail->getQuantationArray($file);
					$qaConverted = $this->automail->convertArrayToInSQL($quantation);
					$invoice = $this->automail->getInvoice($file);
					$invoiceNumber = substr($invoice,3,8);
					
					if ($this->shipping->mapQuantationManyQA($customerCode, $qaConverted, $invoiceNumber) === true && $this->shipping->isAPI($file) === false && count(explode(".", $file)) == 2) {
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
					// $internal = ['to' => ['doc.ds@deestone.com','doc.dsc@deestone.com','nutcha_c@deestone.com','worawut_s@deestone.com'], 'cc' => []];
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
					$inv = $this->automail->getInvoice($m['file']);
					$customerCode = $this->automail->getCustomerCode($m['file']);

					if( $this->shipping->getShippingBody($m['file']) !== false && $this->shipping->getShippingBodyData($inv,$customerCode) !== '') {
						
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
							
							$logging =$this->automail->logging(
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
							$this->automail->loggingEmail($logging,$m['email']['to'],1);
                        	$this->automail->loggingEmail($logging,[$m['internal']],2);

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
                    $this->automail->getBodyReportFailed($fileFailed, 'Shipping document', 'ไฟล์ไม่ถูกต้อง'), 
                    $this->shipping->getMailFailed(),
                    [],
                    [],
                    $files_Outcorrect,
                    $email_failed['failed'][0],
                    $email_failed['failed'][0]
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

	public function sendConfirm($request, $response, $args) {
		try {

			$projectId = 43;
			$root = 'files/shipping_doc_all/doc_please_comfirm';
			$rootTemp = 'temp/shipping_doc_all/doc_please_comfirm';
			$filesOkay = [];
			$fileFailed = [];

			$files = $this->automail->getDirRoot($root);

			foreach ($files as $file) {

				if ($this->shipping->isFormatile($file) === true) {

					$customerCode = $this->automail->getCustomerCode($file);
					$quantation = $this->automail->getQuantationArray($file);
					$qaConverted = $this->automail->convertArrayToInSQL($quantation);
					$invoice = $this->automail->getInvoice($file);
					$invoiceNumber = substr($invoice,3,8);
					
					if ($this->shipping->mapQuantationManyQA($customerCode, $qaConverted, $invoiceNumber) === true && $this->shipping->isAPI($file) === false) {
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
					// $internal = ['to' => ['doc.ds@deestone.com','doc.dsc@deestone.com','nutcha_c@deestone.com','worawut_s@deestone.com'], 'cc' => []];
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
					if( $this->shipping->getShippingBodyConfirm($m['file']) !== false ) {
						
						// echo $this->shipping->getShippingSubjectConfirm($m['file']);
						// echo "<br>"; 
						// echo $this->shipping->getShippingBodyConfirm($m['file']); 
						// echo "<br>";
						// var_dump($email['to']);
						// echo "<br>";
						// echo $m['file'];
						// echo "<hr>";
						$file = [];
						$file[] = $root.$m['file'];
						$sendEmail = $this->email->sendEmail(
							$this->shipping->getShippingSubjectConfirm($m['file']), 
							$this->shipping->getShippingBodyConfirm($m['file']), 
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
								$this->shipping->getShippingSubjectConfirm($m['file']), 
								$this->shipping->getShippingBodyConfirm($m['file']), 
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
                    $this->automail->getBodyReportFailed($fileFailed, 'Shipping document', 'ไฟล์ไม่ถูกต้อง'), 
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

	public function sendAot($request, $response, $args) {
		try {

			$projectId = 3;
			$root = 'files/shipping_document/aot/';
			$fileLists = [];
			$allFiles = [];
			$fileIncorrect = [];
			$fileIncorrectFormat = [];

			$this->automail->initFolder($root);
			$files = $this->automail->getDirRoot($root);
			
			foreach ($files as $file) {
				if (gettype($file) !== 'array') {
					if ($file !== 'Thumbs.db') {
						$allFiles[] = [
							'file_name' => $file,
							'file_size' => $this->automail->Size($root . $file)
						];
					}
				}
			}

			$tmp_file = [];
			$count_file_size = 0;
			$counter_tmp_file = 0;

			foreach ($allFiles as $value) {

				if (substr($value['file_name'], 0, 1) !== '_') {

					$typeOfFileSize = explode(' ', $value['file_size']);

					if ($typeOfFileSize[1] === 'KB') {
						$tmp_file_size = $typeOfFileSize[0] * 0.001;
					} else {
						$tmp_file_size = $typeOfFileSize[0];
					}

					$count_file_size += $tmp_file_size;

					if (round($count_file_size, 2) <= 10.00) { // 10 MB
						$tmp_file[$counter_tmp_file][] = $value['file_name'];
					} else  {
						$count_file_size = 0;
						$counter_tmp_file++;
						$tmp_file[$counter_tmp_file][] = $value['file_name'];
					}
				} else {
					$fileIncorrectFormat[] = $value['file_name'];
				}
			}

			$email = [
				'to' => ['harit_j@deestone.com'],
				'cc' => ['wattana_r@deestone.com','worawut_s@deestone.com','harit_j@deestone.com'],
				'sender' => ['harit_j@deestone.com']
			];

			// echo count($tmp_file);
			// exit();

			for ($i=0; $i < count($tmp_file); $i++) { 

				if (count($tmp_file[$i]) === 0) {
					exit('Folder is empty.' . PHP_EOL);
				}

				$array_file = [];
				foreach ($tmp_file[$i] as $f) {
					array_push($array_file, $root.$f);
				}

				// echo "<pre>".print_r($array_file,true)."</pre>";
				
				// Send To External
				$this->email->sendEmail(
					$this->shipping->getAOTSubject($tmp_file[$i]), 
					$this->shipping->getAOTBody(), 
					$email['to'], 
					[], 
					[], 
					$array_file,
					$email['sender'][0],
					$email['sender'][0]
				);

				// Send To Internal
				$this->email->sendEmail(
					$this->shipping->getAOTSubject($tmp_file[$i]), 
					$this->shipping->getAOTBody(), 
					$email['cc'], 
					[], 
					[], 
					$array_file,
					$email['sender'][0],
					$email['sender'][0]
				);

				echo 'send file : success.<br/>';
	
				$checkDocsInFileName = $this->shipping->isDocsAOTInFileName($tmp_file[$i]);
				// echo count($checkDocsInFileName);
				if ( count($checkDocsInFileName) !== 0 ) {
					// send to account
					// $this->email->sendEmail(
					// 	'Send to Account Test: ' . $this->shipping->getShippingSubject($m['file']), 
					// 	$this->shipping->getShippingBody($m['file']), 
					// 	$acc_fin['to'], 
					// 	[], 
					// 	[], 
					// 	[$root . $m['file']],
					// 	$m['sender'],
					// 	$m['sender']
					// );

				}

				foreach ($tmp_file[$i] as $file) {
					$this->automail->moveFile($root, 'temp/', $file);
				}

			}
			
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function getLogs($request, $response, $args) {
		try {
			$parsedBody = $request->getParsedBody();
			$data = $this->shipping->getLogs($this->datatables->filter($parsedBody));
			$pack = $this->datatables->get($data, $parsedBody);
		
			return $response->withJson($pack);
		} catch (\Exception $e) {
			return ['error' => $e->getMessage()];
		}
	}
}