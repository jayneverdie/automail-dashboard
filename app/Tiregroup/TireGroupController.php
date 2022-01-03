<?php

namespace App\TireGroup;

use App\Common\View;
use App\TireGroup\TireGroupAPI;
use App\Booking\BookingAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;

class TireGroupController {

	public function __construct() {
		$this->view = new View;
		$this->tiregroup = new TireGroupAPI;
		$this->booking = new BookingAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}

	public function runTireGroupBooking($request, $response, $args) {
		try {

			$email_dev = ['to' => ['worawut_s@deestone.com'], 'cc' => []];
			$email_internal_dev = ['to' => ['worawut_s@deestone.com'], 'cc' => []];
			$email_internal_dev2 = ['to' => ['worawut_s@deestone.com'], 'cc' => []];

			$parsedBody = $request->getParsedBody();
			$custcode = $parsedBody["custcode"];

			$stackFileByCustCode = [];
			$stackFileFailed = [];
			$listOfMailDetail = [];
			$mailData = [];
			$fileOkay = [];
			$fileFailed = [];

			if ($custcode === "booking") {
				$projectEmailList = 33;
				$projectId = 17;
				$root = 'files/tiregroup/booking/';
				$rootTemp = 'temp/tiregroup/booking/';
			}else if ($custcode === "booking-revised"){
				$projectEmailList = 33;
				$projectId = 18;
				$root = 'files/tiregroup/booking/';
				$rootTemp = 'temp/tiregroup/booking/';
			}
			
			$files = $this->automail->getDirRoot($root);

			if (count($files)===0) {
				echo "The file does not exist";
				exit();
			}

			foreach ($files as $file) {
				
				$so = $this->automail->getSOFromFileBooking($file);
				$customer = $this->automail->getCustomerCode($file);

				if ($projectId === 17) {
				
					if ($this->automail->isBookingRevise($file) === false) {
						
						if (isset($so) && $this->booking->isSOAndCustomerMatched($so, $customer) === true) {

							$fileOkay[] = [
								'customer' => $customer,
								'so' => $so,
								'file' => $file
							];
						} else {
							$fileFailed[] = $file;
						}	

					}

				}

				if ($projectId === 18) {
				
					if ($this->automail->isBookingRevise($file) === true) {
						
						if (isset($so) && $this->booking->isSOAndCustomerMatched($so, $customer) === true) {

							$fileOkay[] = [
								'customer' => $customer,
								'so' => $so,
								'file' => $file
							];
						} else {
							$fileFailed[] = $file;
						}	

					}

				}

			}

			if (count($fileOkay) !== 0) {

				foreach ($fileOkay as $data) {
					$bookingdata = $this->tiregroup->getSOAndCustomerMatched($data['so']);
					foreach ($bookingdata as $v) {
						$email = $this->tiregroup->getMailCustomer($projectEmailList,$v['CONDITION']);
					}
					$goodData[] = [
						'customer' => $data['customer'],
						'port' => $v['CONDITION'],
						'file' => $data['file'],
						'so' => $data['so'],
						'email' => $email

					];
				}

				 // var_dump($goodData);
				 // exit();

				foreach ($goodData as $m) {
					
					if ($projectId === 17) {
						$subject = $this->booking->getBookingSubject_v2($m['file']);
						$body = $this->booking->getBookingBody_v2($m['file']);
					}else{
						$subject = $this->booking->getBookingSubject_v2($m['file'], 'revised');
						$body = $this->booking->getBookingBody_v2($m['file'], 'revised');
					}
					// echo $subject;
					// exit();

					// send to external
					$sendEmail = $this->email->sendEmail(
						$subject,
						$body,
						// $email_dev['to'], 
						// [],
						$m['email']['to'], 
						$m['email']['cc'],  
						[], 
						[$root . $m['file']],
						$m['email']['sender'],
						$m['email']['sender']
					);
					
					if($sendEmail === true) {
						echo "Message has been sent External\n";

						// insert logs
						$logging = $this->automail->logging(
							$projectId,
							'Message has been sent',
							$m['customer'],
							$m['so'],
							null,
							null,
							null,
							$m['file'],
							'file'
						);

						$this->automail->loggingEmail($logging,$m['email']['to'],1);
						$this->automail->loggingEmail($logging,$m['email']['cc'],2);
						
						// send to internal
						$sendEmailInternal = $this->email->sendEmail(
							$subject,
							$body,
							// $email_internal_dev['to'],  
							$m['email']['internal'],
							[], 
							[], 
							[$root . $m['file']],
							$m['email']['sender'],
							$m['email']['sender']
						);

						// send to internal2
						$sendEmailInternal2 = $this->email->sendEmail(
							$this->booking->getBookingSubject_internal($m['file'],$m['customer'], $Type = 'New'),
							$this->booking->getBookingBody_v4($m['file'],$m['customer']),
							$m['email']['internal2'],
							[],
							[], 
							[$root . $m['file']],
							//$m['email']['sender'],
							//$m['email']['sender']
							'kanokporn_s@deestone.com',
							'kanokporn_s@deestone.com'
						);

						if($sendEmailInternal === true) {
							$this->automail->loggingEmail($logging,$m['email']['internal'],1);
							echo "Message has been sent internal\n";
						}else{
							echo $sendEmailInternal;
						}

						// sendSucess movefile
						$this->automail->initFolder($rootTemp, 'logs');
						$this->automail->moveFile($root, $rootTemp, 'logs/', $m['file']);

					}else{
						echo $sendEmail;
						// sendfailed movefile
						$this->automail->initFolder($root, 'failed');
						$this->automail->moveFile($root, $root, 'failed/', $m['file']);
					}
					
				}
			}

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function runTireGroupShipping($request, $response, $args) {
		try {

			$email_dev = ['to' => ['harit_j@deestone.com'], 'cc' => []];
			$email_internal_dev = ['to' => ['harit_j@deestone.com'], 'cc' => []];
			$email_internal_acc = ['to' => ['harit_j@deestone.com'], 'cc' => []];

			$parsedBody = $request->getParsedBody();
			$custcode = $parsedBody["custcode"];
			// var_dump($parsedBody); exit();

			$stackFileByCustCode = [];
			$stackFileFailed = [];
			$listOfMailDetail = [];
			$mailData = [];

			if ($custcode === "shipping") {
				$projectEmailList = 33;
				$projectId = 14;
				$root = 'files/tiregroup/shipping_doc/';
				$rootTemp = 'temp/tiregroup/shipping_doc/';
			}

			$files = $this->automail->getDirRoot($root);

			if (count($files)===0) {
				echo "The file does not exist";
				exit();
			}

			foreach ($files as $file) {
				
				$customer = $this->automail->getCustomerCode($file);
				$quantation = $this->automail->getQuantation($file);
				$voucher = $this->automail->getInvoice($file);
				$voucherloop = $this->automail->getInvoice($voucher);
				$vouchercutstr = substr($voucherloop,3,8);
				
				$port = $this->tiregroup->getPort();

				if ($this->tiregroup->mapQuantation($port,$quantation,$vouchercutstr)===true) {
					
					$stackFileByCustCode[] = [
						'customer' => $customer,
						'quantation' => $quantation,
						'voucher' => $vouchercutstr,
						'file' => $file
					];
				} else {

					$stackFileFailed[] = $file;
				}
			}

			// var_dump($stackFileByCustCode);
			// exit();

			if (count($stackFileByCustCode) !== 0) {

				foreach ($stackFileByCustCode as $data) {
					
					$shippingdata = $this->tiregroup->mapQuantationdata($data['quantation'],$data['voucher']);
					$email = $this->tiregroup->getMailCustomer($projectEmailList,$shippingdata[0]['CONDITION']);

					$goodData[] = [
						'customer' => $data['customer'],
						'file' => $data['file'],
						'port' => $shippingdata[0]['CONDITION'],
						'qa' => $data['quantation'],
						'voucher' => $data['voucher'],
						'email' => $email
					];
					
				}

				// var_dump($goodData);
				// exit();

				foreach ($goodData as $m) {

					// send to external
					$sendEmail = $this->email->sendEmail(
						$this->tiregroup->getShippingSubjectV2($m['file']),
						$this->tiregroup->getShippingBodyV2($m['file']),
						$m['email']['to'], 
						$m['email']['cc'], 
						[], 
						[$root . $m['file']],
						$m['email']['sender'],
						$m['email']['sender']
					);
					
					if($sendEmail === true) {
						echo "Message has been sent External\n";

						// insert logs
						$logging = $this->automail->logging(
							$projectId,
							'Message has been sent',
							$m['customer'],
							null,
							null,
							$m['qa'],
							$m['voucher'],
							$m['file'],
							'file'
						);

						$this->automail->loggingEmail($logging,$m['email']['to'],1);
						$this->automail->loggingEmail($logging,$m['email']['cc'],2);

						// send to internal
						$sendEmailInternal = $this->email->sendEmail(
							$this->tiregroup->getShippingSubjectV2($m['file']),
							$this->tiregroup->getShippingBodyV2($m['file']),
							$m['email']['internal'],
							[], 
							[], 
							[$root . $m['file']],
							$m['email']['sender'],
							$m['email']['sender']
						);

						// send to acc
						$__fileWithDocs = $this->tiregroup->getShippingDOCSInName([$m['file']]);

						if (count($__fileWithDocs) !== 0) 
						{
							$sendEmailgroupshipping = $this->email->sendEmail(
								$this->tiregroup->getShippingSubjectV2($m['file']),
								$this->tiregroup->getShippingBodyV2($m['file']),
								[$m['email']['groupshipping']],
								[], 
								[], 
								[$root . $m['file']],
								$m['email']['sender'],
								$m['email']['sender']
							);
						}

						if($sendEmailInternal === true) {
							$this->automail->loggingEmail($logging,$m['email']['internal'],1);
							$this->automail->loggingEmail($logging,[$m['email']['groupshipping']],1);
							echo "Message has been sent internal\n";
						}else{
							echo $sendEmailInternal;
						}

						// sendSucess movefile
						$this->automail->initFolder($rootTemp, 'logs');
						$this->automail->moveFile($root, $rootTemp, 'logs/', $m['file']);

					}else{
						echo $sendEmail;
						// sendfailed movefile

						$sendEmailgroupshipping = $this->email->sendEmail(
								$this->tiregroup->getShippingSubjectV2($m['file']),
								$this->tiregroup->getShippingBodyV2($m['file']),
								[$m['email']['sender']],
								[], 
								[], 
								[$root . $m['file']],
								$m['email']['sender'],
								$m['email']['sender']
							);

						$this->automail->initFolder($root, 'failed');
						$this->automail->moveFile($root, $root, 'failed/', $m['file']);
					}
					
				}
			}

		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function runTireGroupLoadingPlan($request, $response, $args) {

		$dataLoadingPlan = $this->tiregroup->getDataLoadingPlan();

		if (count($dataLoadingPlan)===0) {
			echo "Data Is Not Found!";
			exit();
		}

		foreach ($dataLoadingPlan as $key => $value) {
			
			$customer 			= $value['CUSTACCOUNT'];
			$customer_name 		= $value['NAME'];
			$std20 				= $value['STD20'];
			$std40 				= $value['STD40'];
			$hc40 				= $value['HC40'];
			$lcl				= $value['LCL'];
			$hc45				= $value['HC45'];
			$quotationid		= $value['QUOTATIONID'];
			$po 				= $value['CustomerRef'];
			$salesid 			= $value['SALESID'];
			$port 				= $value['DSG_TOPORTDESC'];
			$avaliable      	= $value['DSG_AVAILABLEDATE'];
			$paymdaterequire 	= $value['DSG_PaymDateRequire'];
			$lastshipment 		= $value['Lastshipment'];
			$expirydate 		= $value['Expirydate'];
			$currency 			= $value['CURRENCYCODEISO'];
			$amount 			= number_format($value['DSG_AMOUNTREQUIRE'],2);
			$agent 				= $value['DSG_AGENT'];
			$requestshipdate    = $value['DSG_REQUESTSHIPDATE'];
			$noted 				= $value['DSG_NoteAutoMail'];
			$paymenttype 		= $value['PaymentType'];

			$getPortLoadingPlan = $this->tiregroup->loadingplandata($salesid);
			echo $getPortLoadingPlan['CONDITION'][0];
		}
	}

	public function runTireGroupVessel($request, $response, $args) {
		try {

			$email_dev = ['to' => ['harit_j@deestone.com'], 'cc' => []];
			$email_internal_dev = ['to' => ['worawut_s@deestone.com'], 'cc' => []];
			$email_internal_dev2 = ['to' => ['worawut_s@deestone.com'], 'cc' => []];

			$parsedBody = $request->getParsedBody();
			$params = $parsedBody["params"];
			$projectEmailList = 33;
			$projectId = 15;

			$time_set = [
			  'A' => [
			    'start_date' => date('Y-m-d', strtotime("-1 day")),
			    'end_date' => date('Y-m-d'),
			    'start_time' => '17:40:01',
			    'end_time' => '23:59:59'
			  ],
			  'B' => [
			    'start_date' => date('Y-m-d'),
			    'end_date' => date('Y-m-d'),
			    'start_time' => '00:00:01',
			    'end_time' => '12:00:00'
			  ],
			  'C' => [
			    'start_date' => date('Y-m-d'),
			    'end_date' => date('Y-m-d'),
			    'start_time' => '12:00:01',
			    'end_time' => '16:00:00'
			  ],
			  'D' => [
			    'start_date' => date('Y-m-d'),
			    'end_date' => date('Y-m-d'),
			    'start_time' => '16:00:01',
			    'end_time' => '17:40:00'
			  ],
			  'X' => [
			    'start_date' => date('Y-m-d'),
			    'end_date' => date('Y-m-d'),
			    'start_time' => '00:00:01',
			    'end_time' => '17:40:00'
			  ]
			];
			// $time_set = [
			//   'A' => [
			//     'start_date' => date('Y-m-d', strtotime("-1 day")),
			//     'end_date' => date('Y-m-d'),
			//     'start_time' => '17:40:01',
			//     'end_time' => '23:59:59'
			//   ],
			//   'B' => [
			//     'start_date' => '2019-07-25',
			//     'end_date' => '2019-07-25',
			//     'start_time' => '00:00:01',
			//     'end_time' => '12:00:00'
			//   ],
			//   'C' => [
			//     'start_date' => date('Y-m-d'),
			//     'end_date' => date('Y-m-d'),
			//     'start_time' => '12:00:01',
			//     'end_time' => '16:00:00'
			//   ],
			//   'D' => [
			//     'start_date' => date('Y-m-d'),
			//     'end_date' => date('Y-m-d'),
			//     'start_time' => '16:00:01',
			//     'end_time' => '17:40:00'
			//   ],
			//   'X' => [
			//     'start_date' => date('Y-m-d'),
			//     'end_date' => date('Y-m-d'),
			//     'start_time' => '00:00:01',
			//     'end_time' => '17:40:00'
			//   ]
			// ];

			$getVesselData = $this->tiregroup->getVesselData($time_set[$params]);

			// echo "<pre>".print_r($getVesselData,true)."</pre>";
			// exit();

			foreach ($getVesselData as $v) {
				if ($params === 'X') {
					$email = ['to' => ['fn_export@deestone.com'], 'cc' => []];
				}

				$email = $this->tiregroup->getMailCustomer($projectEmailList,$v['CONDITION']);

				$subject = $this->tiregroup->getVesselSubject($v['QUOTATIONID'], $v['INVNO'], $v['CUSTNAME']);
				$body = $this->tiregroup->getVesselBody(
			      $v['CUSTNAME'],
			      $v['QUOTATIONID'],
			      $v['CUSTOMERREF'],
			      $v['TOPORT'],
			      date('d/m/Y', strtotime( str_replace('/', '-', $v['BEFORE_ETD']) )),
			      date('d/m/Y', strtotime($v['AFTER_ETD'])),
			      date('d/m/Y', strtotime( str_replace('/', '-', $v['BEFORE_ETA']) )),
			      date('d/m/Y', strtotime($v['AFTER_ETA'])),
			      $v['INVNO'],
			      $v['DSG_SALESID'],
			      $v['BEFORE_VESSEL'],
			      $v['BEFORE_FEEDER'],
			      $v['AFTER_VESSEL'],
			      $v['AFTER_FEEDER']
			    ); 

				// send to external
				$sendEmail = $this->email->sendEmail(
					$subject,
					$body,
					$email_dev['to'], 
					[], 
					[], 
					[],
					$email['sender'],
					$email['sender']
				);
					
				if($sendEmail === true) {
					echo "Message has been sent External\n";

					$logging = $this->automail->logging(
						$projectId,
						'Message has been sent',
						'C-1089',
						$v['DSG_SALESID'],
						null,
						$v['QUOTATIONID'],
						null,
						null,
						'Ax'
					);

					$this->automail->loggingEmail($logging,$email_dev['to'],1);
					$this->automail->loggingEmail($logging,$email_dev['cc'],2);

					// send to internal
					$sendEmailInternal = $this->email->sendEmail(
						$subject,
						$body,
						$email_internal_dev['to'], 
						[], 
						[], 
						[],
						$email['sender'],
						$email['sender']
					);

					if ($sendEmailInternal === true) {
						echo "Message has been sent Internal\n";
					}else{
						echo $sendEmailInternal;
					}

				}else{
					echo $sendEmail;
				}

				

			}

		} catch (\Exception $e) {
			return $e->getMessage();	
		}
	}

	public function runTireGroupAirwaybill($request, $response, $args) {
		try {
			
			$email_dev = ['to' => ['harit_j@deestone.com'], 'cc' => []];
			$email_internal_dev = ['to' => ['worawut_s@deestone.com'], 'cc' => []];
			$email_internal_dev2 = ['to' => ['worawut_s@deestone.com'], 'cc' => []];
			
			$parsedBody = $request->getParsedBody();
			$params = $parsedBody["params"];
			$projectEmailList = 33;
			$projectId = 15;

			$time_set = [
			  'A' => [
			    'start_date' => date('Y-m-d', strtotime("-1 day")),
			    'end_date' => date('Y-m-d'),
			    'start_time' => '17:40:01',
			    'end_time' => '23:59:59'
			  ],
			  'B' => [
			    'start_date' => date('Y-m-d'),
			    'end_date' => date('Y-m-d'),
			    'start_time' => '00:00:01',
			    'end_time' => '12:00:00'
			  ],
			  'C' => [
			    'start_date' => date('Y-m-d'),
			    'end_date' => date('Y-m-d'),
			    'start_time' => '12:00:01',
			    'end_time' => '16:00:00'
			  ],
			  'D' => [
			    'start_date' => date('Y-m-d'),
			    'end_date' => date('Y-m-d'),
			    'start_time' => '16:00:01',
			    'end_time' => '17:40:00'
			  ],
			  'X' => [
			    'start_date' => date('Y-m-d'),
			    'end_date' => date('Y-m-d'),
			    'start_time' => '00:00:01',
			    'end_time' => '17:40:00'
			  ]
			];

			$getAirWayBillData = $this->tiregroup->getAirWayBillData($time_set[$params]);

			// echo "<pre>".print_r($getAirWayBillData,true)."</pre>";
			// exit();

			foreach ($getAirWayBillData as $v) {
				
				$email = $this->tiregroup->getMailCustomer($projectEmailList,$v['CONDITION']);

				$getAirWayDocCheck = $this->tiregroup->getDocumentcheck($v['DSG_SALESID']);

				$AWB_check = $this->tiregroup->AWBcheckSend($v['DSG_SALESID']);

				if ($getAirWayDocCheck === true) {
					
					if ($AWB_check === true) {
						
						$subject = $this->tiregroup->getAirWayBillSubject(
							$v['QUOTATIONID'], $v['INVNO'], $v['CUSTNAME'],'revised'
						);
						$body = $this->tiregroup->getAirWayBillBody2(
					        $v['DSG_COURIERNAME'],
					        $v['DSG_AWB_NO'],
					        $v['CUSTNAME'],
					        $v['QUOTATIONID'],
					        $v['CUSTOMERREF'],
					        $v['DSG_SALESID'],
					        date('d/m/Y', strtotime($v['DSG_ETD'])),
					        date('d/m/Y', strtotime($v['DSG_ETA'])),
					        $v['INVNO'],
					        $v['TOPORT'],
					        $v['AGENT'],
					        $v['SHIPPINGLINE'],
					        $v['CUSTACCOUNT']
					    ); 

					}else{

						$subject = $this->tiregroup->getAirWayBillSubject(
							$v['QUOTATIONID'], $v['INVNO'], $v['CUSTNAME'],'new'
						);
						$body = $this->tiregroup->getAirWayBillBody1(
					        $v['DSG_COURIERNAME'],
					        $v['DSG_AWB_NO'],
					        $v['CUSTNAME'],
					        $v['QUOTATIONID'],
					        $v['CUSTOMERREF'],
					        $v['DSG_SALESID'],
					        date('d/m/Y', strtotime($v['DSG_ETD'])),
					        date('d/m/Y', strtotime($v['DSG_ETA'])),
					        $v['INVNO'],
					        $v['TOPORT'],
					        $v['AGENT'],
					        $v['SHIPPINGLINE'],
					        $v['CUSTACCOUNT']
					    ); 
					}

				}else{
					
					if ($AWB_check === true) {
						
						$subject = $this->tiregroup->getAirWayBillSubject(
							$v['QUOTATIONID'], $v['INVNO'], $v['CUSTNAME'],'revised'
						);
						$body = $automail->getAirWayBillBody4(
					        $v['DSG_AWB_NO'],
					        $v['CUSTNAME'],
					        $v['QUOTATIONID'],
					        $v['CUSTOMERREF'],
					        $v['DSG_SALESID'],
					        date('d/m/Y', strtotime($v['DSG_ETD'])),
					        date('d/m/Y', strtotime($v['DSG_ETA'])),
					        $v['INVNO'],
					        $v['TOPORT'],
					        $v['AGENT'],
					        $v['SHIPPINGLINE'],
					        $v['CUSTACCOUNT']
					    );

					}else{
						
						$subject = $this->tiregroup->getAirWayBillSubject(
							$v['QUOTATIONID'], $v['INVNO'], $v['CUSTNAME'],'new'
						);
						$body = $this->tiregroup->getAirWayBillBody3(
					        $v['DSG_AWB_NO'],
					        $v['CUSTNAME'],
					        $v['QUOTATIONID'],
					        $v['CUSTOMERREF'],
					        $v['DSG_SALESID'],
					        date('d/m/Y', strtotime($v['DSG_ETD'])),
					        date('d/m/Y', strtotime($v['DSG_ETA'])),
					        $v['INVNO'],
					        $v['TOPORT'],
					        $v['AGENT'],
					        $v['SHIPPINGLINE'],
					        $v['CUSTACCOUNT']
					    );

					}

				}

				// Senmail
				echo $subject;
				echo "<br>";
				echo $body;
			}

		} catch (\Exception $e) {
			return $e->getMessage();	
		}
	}
}