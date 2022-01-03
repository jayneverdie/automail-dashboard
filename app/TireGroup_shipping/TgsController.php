<?php

namespace App\TireGroup_shipping;

use App\Common\View;
use App\TireGroup_shipping\TgsAPI;
use App\Common\Automail_TriegroupShipping;
use App\Email\EmailAPI;
use App\Common\Datatables;

class TgsController {

	public function __construct() {
		$this->view = new View;
		$this->Tgs = new TgsAPI;
		$this->automail = new Automail_TriegroupShipping;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}

	public function all($request, $response, $args) {
		return $this->view->render('pages/TireGroup_shipping/all');
	}

	public function getLogs($request, $response, $args) {
		try {
			$parsedBody = $request->getParsedBody();
			$data = $this->Tgs->getLogs($this->datatables->filter($parsedBody));
			$pack = $this->datatables->get($data, $parsedBody);

			return $response->withJson($pack);
		} catch (\Exception $e) {
			return ['error' => $e->getMessage()];
		}
	}

	public function allSend($request, $response, $args) {
		try {

			$projectId = 14;
			$root = 'files/TireGroup_shipping_document/';
			$filesOkay = [];
			$fileFailed = [];

			$this->automail->initFolder($root);

			$files = $this->automail->getDirRoot($root);

			foreach ($files as $file) {
				$customerCode = $this->automail->getCustomerCode($file);
				$quantation = $this->automail->getQuantationArray($file);
				$qaConverted = $this->automail->convertArrayToInSQL($quantation);
				$invoice = $this->automail->getInvoice($file);
				$invoiceNumber = substr($invoice,3,8);

				// if ($this->shipping->isSurrender($file) === false) {
				// 	if ($this->shipping->mapQuantationManyQA($customerCode, $qaConverted, $invoiceNumber) === true) {
				// 		$filesOkay[] = [
				// 			'customer' => $customerCode,
				// 			'file' => $file
				// 		];
				// 	} else {
				// 		$fileFailed[] = $file;
				// 	}
				// }

				if($this->$automail->mapQuantation($quantation, $invoiceNumber) === true) { // false
		// if(true){
			$quantationloop = $this-> $automail->getCustomerQuantation($quantation);
			// good file
			$filesOkay[] = [
				'customer' => $customerCode,
				'quantation' => $quantationloop,
				'voucher' => $invoiceNumber,
				'file' => $file
			];
		} else {
			// wrong format
			$fileFailed[] = $file;
		}
			}

			if (count($filesOkay) > 0) {
				foreach ($filesOkay as $f) {
					// $email = $this->automail->getCustomerMail($f['customer']);
				//	$email = ['to' => ['wattana_r@deestone.com'], 'cc' => []];
				$emailgroup = $this->$automail->getCustomerMail($f['customer']); // func  to,cc หาใคร?
				$shippingdata = $this->$automail->mapQuantationdata($f['quantation'],$value['voucher']); // เช็คType File

					$success[] = [
						'customer' => $f['customer'],
						'email' => $email,
						'file' => $f['file'],
						'sender' => $this->automail->getEmailFromCustomerCode($f['customer']), //mail sender
						'internal' => 'shippingdoc@deestone.com'
					];

					foreach ($shippingdata as $v) {
						// $email = ['to' => ['nattapon_t@deestone.com'],
 						//              'cc' => ['nattapon_t@deestone.com']];
						if ($v['CONDITION'] === 'PTO') {
							//$email = $json["tiregroup"]["barmas"];
						}
						if ($v['CONDITION'] === 'SVA') {
							//$email = $json["tiregroup"]["jzarate"];
						}
						if ($v['CONDITION'] === 'SJU' || $v['CONDITION'] === 'PEB' || $v['CONDITION'] === 'GAI') {
							//	$email = $json["tiregroup"]["jzarate2"];
						}
						if ($v['CONDITION'] === 'ABE' || $v['CONDITION'] === 'CRR' || $v['CONDITION'] === 'DUL' || $v['CONDITION'] === 'GRN' || $v['CONDITION'] === 'HOU' || $v['CONDITION'] === 'LBE' || $v['CONDITION'] === 'MES' || $v['CONDITION'] === 'FRS' || $v['CONDITION'] === 'STN') {
							//	$email = $json["tiregroup"]["lluvitza"];
						}
						if ($v['CONDITION'] === 'BBA' || $v['CONDITION'] === 'CGO' || $v['CONDITION'] === 'ORK' || $v['CONDITION'] === 'DUB' || $v['CONDITION'] === 'GUA' || $v['CONDITION'] === 'HER' || $v['CONDITION'] === 'KLJ' || $v['CONDITION'] === 'KOP' || $v['CONDITION'] === 'LIV' || $v['CONDITION'] === 'PUE' || $v['CONDITION'] === 'RTM' || $v['CONDITION'] === 'SPD' || $v['CONDITION'] === 'SOU' || $v['CONDITION'] === 'VAR' || $v['CONDITION'] === 'BAJ' || $v['CONDITION'] === 'BUD' || $v['CONDITION'] === 'LIT' || $v['CONDITION'] === 'MRS' || $v['CONDITION'] === 'STX' || $v['CONDITION'] === 'RIG') {
							//		$email = $json["tiregroup"]["lluvitza2"];
						}
						if ($v['CONDITION'] === 'MIA') {
							//	$email = $json["tiregroup"]["milayconde"];
						}
						if ($v['CONDITION'] === 'COG') {
							//	$email = $json["tiregroup"]["rita"];
						}
						if ($v['CONDITION'] === 'PJD' || $v['CONDITION'] === 'WMT' || $v['CONDITION'] === 'LAX') {
							//	$email = $json["tiregroup"]["rociograndez"];
						}
						if ($v['CONDITION'] === 'BAQ' || $v['CONDITION'] === 'BUN' || $v['CONDITION'] === 'LAD' || $v['CONDITION'] === 'ZLO' || $v['CONDITION'] === 'MAM' || $v['CONDITION'] === 'MON' || $v['CONDITION'] === 'REY' || $v['CONDITION'] === 'NUL' || $v['CONDITION'] === 'SNL') {
							//	$email = $json["tiregroup"]["rociograndez2"];
						}
						if ($v['CONDITION'] === 'ARI' || $v['CONDITION'] === 'ASU' || $v['CONDITION'] === 'CAU' || $v['CONDITION'] === 'CAU' || $v['CONDITION'] === 'GUY' || $v['CONDITION'] === 'RHN' || $v['CONDITION'] === 'SIO' || $v['CONDITION'] === 'PER' || $v['CONDITION'] === 'SAC') {
							//	$email = $json["tiregroup"]["taniagonzalez"];
						}
					}
				}

				foreach ($success as $m) {
					if( $this->Tgs->getShippingBody($m['file']) !== false ) {

						$this->email->sendEmail(
							$this->Tgs->getShippingSubject($m['file']),
							$this->Tgs->getShippingBody($m['file']),
							$email['to'],
							[],
							[],
							[$root . $m['file']],
							$m['sender'],
							$m['sender']
						);

						echo 'send file: ' . $m['file'] . ' success.<br/>';

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

						$checkDocsInFileName = $this->Tgs->isDocsInFileName($m['file']);

						if ( $checkDocsInFileName ===  true ) {
							// send to account
							$this->email->sendEmail(
								'Send to Account Test: ' . $this->Tgs->getShippingSubject($m['file']),
								$this->Tgs->getShippingBody($m['file']),
								$email['to'],
								[],
								[],
								[$root . $m['file']],
								$m['sender'],
								$m['sender']
							);

							// send to finance
							$this->email->sendEmail(
								'Send to Finance Test: ' . $this->shipping->getShippingSubject($m['file']),
								$this->shipping->getShippingBody($m['file']),
								$email['to'],
								[],
								[],
								[$root . $m['file']],
								$m['sender'],
								$m['sender']
							);
						}

						 $this->automail->moveFile($root, 'temp/', $m['file']);
					}
				}
			}

			if (count($fileFailed) > 0) {

				$this->email->sendEmail(
					$this->automail->getSubjectReportFailed(),
					$this->automail->getBodyReportFailed($fileFailed, 'Shipping document', 'ไฟล์ไม่ถูกต้อง'),
					$this->shipping->getMailFailed(),
					[],
					[],
					$this->automail->updateFilePath($root, $fileFailed),
					'ea_devteam@deestone.com',
					'ea_devteam@deestone.com'
				);

				foreach ($fileFailed as $f) {
					echo 'send file: ' . $f . ' failed.<br/>';
					$this->automail->moveFile($root, 'failed/', $f);
					$this->automail->logging(
						$projectId,
						'ไฟล์ไม่ถูกต้อง',
						null,
						null,
						null,
						null,
						null,
						$f,
						'file'
					);
				}
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}




}
