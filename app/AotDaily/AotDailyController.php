<?php

namespace App\AotDaily;

use App\Common\View;
use App\AotDaily\AotDailyAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AotDailyController {

	public function __construct() {
		$this->view = new View;
		$this->aotdaily = new AotDailyAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}
	public function booking($request, $response, $args)
    {
         try {
            // code
			// $output = shell_exec("copy \\\\lungryn\automail\booking_confirmation\aot_daily_report\AOT_Booking_Daily_Report.xls
			//  files\\aot\\booking_daily_report\\AOT_Booking_Daily_Report.xls");
			// echo $output;
			// exit();
			

			$projectId = 38;
			$root = 'D:\automail\booking_confirmation\aot_daily_report\\';
			// $root = 'files/aot/booking_daily_report/';
			$rootTemp = 'temp/aotdaily/booking/';
			// $fileOkay = [];
			// $SOSend = "";
			$files = $this->automail->getDirRoot($root);
			$getMail = $this->aotdaily->getMailCustomer($projectId);

			// echo "\n" . $root . "\n";
			// echo "\n";
			// echo count($files) . "\n";
			// exit();

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
			sort($allFiles);
			// echo "<pre>".print_r($allFiles,true)."</pre>";
            // exit;
			foreach ($allFiles as $a) 
			{
				if ($a['file_name'] === "AOT_Booking_Daily_Report.xls") 
				{
					// echo "<pre>".print_r("1",true)."</pre>";
					// exit;
					$body = $this->aotdaily->getBookingBody();
					$subject = $this->aotdaily->getBookingSubject();

					// echo "<pre>"."sender ".print_r($getMail['sender'][0],true)."</pre>";
					// echo "<pre>"."to_ex ".print_r($getMail['to'],true)."</pre> <br/>";
					// echo "<pre>"."to ".print_r($getMail['internal'],true)."</pre> <br/>";
					// echo "<pre>".print_r($root . $a['file_name'],true)."</pre>";
					// echo "<pre>".print_r($subject,true)."</pre>";
					// echo "<pre>".print_r($body,true)."</pre>";
						
					// exit;
				
					$sendEmail = $this->email->sendEmail(
						$subject,
						$body,
						$getMail['to'],
						[],
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
						//$this->automail->loggingEmail($logging,$getMail['cc'],2); //cc
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
						// echo "<pre>";
						//  print_r($getMail);
						//  echo "</pre>";
				}
				// else
				// {
				// 	//echo "<pre>".print_r("2",true)."</pre>";
				// 	// exit;
				// }
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

	// public function booking($request, $response, $args) 
	// {
	// 	try 
	// 	{
	// 		$projectId = 38;
	// 		$root = 'D:\aot_daily\\';
	// 		//'D:\aot_daily'
	// 		//'\\\\lungryn\automail\booking_confirmation\aot_daily_report\\'; 
	// 		//192.168.90.35 \\\\lungryn\automail\\booking_confirmation\\aot_daily_report\\
	// 		// D:\automail\booking_confirmation\aot_daily_report   
	// 		$rootTemp = 'temp/aotdaily/booking/';
	// 		$fileOkay = [];
	// 		$SOSend = "";
	// 		$files = $this->automail->getDirRoot($root);
	// 		$getMail = $this->aotdaily->getMailCustomer($projectId);
			
	// 		// echo count($files);
	// 		// exit();

	// 		if (count($files)===0) 
	// 		{
	// 			echo "The file does not exist";
	// 			exit();
	// 		}
			
			
	// 		if (count($files) !== 0) 
	// 		{

	// 			$body = $this->aotdaily->getBookingBody();
	// 			$subject = $this->aotdaily->getBookingSubject();

	// 			// echo "<pre>"."sender ".print_r($getMail['sender'][0],true)."</pre>";
	// 			// echo "<pre>"."to_ex ".print_r($getMail['to'],true)."</pre> <br/>";
	// 			// echo "<pre>"."to ".print_r($getMail['internal'],true)."</pre> <br/>";
	// 			// echo "<pre>".print_r($root . $files[0],true)."</pre>";
	// 			// echo "<pre>".print_r($subject,true)."</pre>";
	// 			// echo "<pre>".print_r($body,true)."</pre>";
				
	// 			// exit;
				
	// 			$sendEmail = $this->email->sendEmail(
	// 				$subject,
	// 				$body,
	// 				$getMail['to'],
	// 				[],
	// 				[],
	// 				[$root . $files[0]],
	// 				$getMail['sender'][0],
	// 				$getMail['sender'][0]
	// 			);
				
	// 			if ($sendEmail == true) 
	// 			{
	// 				echo "Message has been sent !!\n";
	// 				$sendEmailInternal = $this->email->sendEmail(
	// 					$subject,
	// 					$body,
	// 					$getMail['internal'],
	// 					[],
	// 					[],
	// 					[$root . $files[0]],
	// 					$getMail['sender'][0],
	// 					$getMail['sender'][0]
	// 				);


	// 				$logging = $this->automail->logging(
	// 					$projectId,
	// 					'Message has been sent',
	// 					null,
	// 					null,
	// 					null,
	// 					null,
	// 					null,
	// 					$files,
	// 					'File'
	// 				);

	// 				$this->automail->loggingEmail($logging,$getMail['to'],1); //1To
	// 				//$this->automail->loggingEmail($logging,$getMail['cc'],2); //cc
	// 				if ($sendEmailInternal == true) {
	// 					$this->automail->loggingEmail($logging,$getMail['internal'],1);
	// 				}
	// 				$this->automail->initFolder($rootTemp, 'logs');
	// 				$this->automail->moveFile($root, $rootTemp, 'logs/', $files[0]);

					
	// 			}
	// 			else
	// 			{
	// 				echo $sendEmail;
	// 				// sendfailed movefile
	// 				$this->automail->initFolder($root, 'failed');
	// 				$this->automail->moveFile($root, $root, 'failed/', $files[0]);
	// 			}
	// 				// echo "<pre>";
	// 				//  print_r($getMail);
	// 				//  echo "</pre>";
	// 		}
	// 	} 
	// 	catch (\Exception $e) 
	// 	{
	// 		echo $e->getMessage();
	// 	}
    // }


}
