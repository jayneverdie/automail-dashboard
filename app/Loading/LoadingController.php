<?php

namespace App\Loading;

use App\Common\View;
use App\Loading\LoadingAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;

class LoadingController {

	public function __construct() {
		$this->view = new View;
		$this->loading = new LoadingAPI;
		$this->automail = new Automail;
		$this->emailApi = new EmailAPI;
		$this->datatables = new Datatables;
	}

	public function send($request, $response, $args) 
	{
		try 
		{
			$projectId = 24;
			$root = 'files/loading/';
			$rootTemp = 'temp/loading/';
			$filesOkay = [];
			$fileFailed = [];

			$files = $this->automail->getDirRoot($root);
			

			foreach ($files as $f) 
			{
				$so = $this->loading->getCustomFromFileECS($f);
				$iscus = $this->loading->IsCustomFromFileECS($f);

				if(count($so) !==0 && $this->loading->IsCustomFromFileECS($f))
				{
					if($this->loading->IsFormatRevised($f))
					{
						$fileOkay[] =
						[
							"file" => $f,
							"type" => 'revise'
						];
					}
					else
					{
						$fileOkay[] =
						[
							"file" => $f,
							"type" => 'normal'
						];
					}
				}
				else
				{
					$fileFailed[] =
					[
						"file" => $f,
						"note" => 'ชื่อไฟล์ไม่ถูกต้อง'
					];
				}
				
			}
			// 

			if (count($fileOkay) !== 0) 
			{
				foreach ($fileOkay as $data) 
				{
					$voucher = $this->loading->explodeFile($data);

					$isvou = $this->loading->IsVoucher($voucher["company"],$voucher["year"],$voucher["no"]);
					if($isvou == true)
					{
						$get_so =  $this->loading->getSalesOrder($voucher["company"],$voucher["year"],$voucher["no"]);
						
						// echo "<pre>".print_r($get_so,true)."</pre>";

						
						foreach ($get_so as $so) 
						{
							$voucher_ex = $this->loading->explodeVoucher($so["DSG_LOADINGPLANT"]);
							$send_to = [];
							$send_cc = [];
							foreach ($voucher_ex as $v) 
							{
								$email = $this->loading->getEmail($projectId,$v,$so["DSG_CheckerCustomer"]);

								foreach ($email["to"]  as $e) 
								{
									if(in_array($e,$send_to,true) === false)
									{
										$send_to[] = $e;
									}
								}
								foreach ($email["cc"]  as $e) 
								{
									if(in_array($e,$send_to,true) === false)
									{
										$send_cc[] = $e;
									}
								}
								
								$sender = $email["sender"];
							}

							// echo "<pre>".print_r($send_to,true)."</pre>";
							// echo "<pre>".print_r($send_cc,true)."</pre>";
							// echo "<pre>".print_r($sender,true)."</pre>";
							// echo "<pre>".print_r($so,true)."</pre>";
							
							$good_data[] =[
								"to" => $send_to,
								"cc" => $send_cc,
								"file"=> $data["file"],
								"subject" => $this->loading->getSubject($data,$so),
								"body" => $this->loading->getBody($so),
								"root" => $root,
								"sender" => $sender,
								"SO" => $so["SALESID"],
								"INVOICE" => $so["INVOICE"]
							];

							// echo "<pre>".print_r($good_data,true)."</pre>";
						}
					}
					else
					{
						// echo "<pre>".print_r($data['file']."   Voucher ไม่มีในระบบ AX",true)."</pre>";
						$fileFailed[] =
						[
							"file" => $data["file"],
							"note" => "voucher ไม่มีในระบบ AX"
						];
					}

				}		
				
				// exit;
				// echo "<pre>".print_r($good_data,true)."</pre>";
				// echo "<pre>".print_r($root,true)."</pre>";

				if(isset($good_data)&& count($good_data)>0)
				{
					// echo "sendmail";
					foreach ($good_data as $data) 
					{
						// echo "<pre>".print_r($data['to'],true)."</pre>";
						// echo "<br>";
						// echo "<pre>".print_r($data['cc'],true)."</pre>";
						// echo "<br>";
						// echo "<pre>".print_r($data['sender'],true)."</pre>";
						// echo "<br>";
						// echo "<pre>".print_r([$root . $data['file']],true)."</pre>";
						// echo "<br>";
						// echo "<pre>".print_r($data['subject'],true)."</pre>";
						// echo "<br>";
						// echo "<pre>".print_r($data['body'],true)."</pre>";
						// echo "<br>";

						$sendEmail = $this->emailApi->sendEmail(
							$data['subject'],
							$data['body'],
							$data['to'],
							$data['cc'],
							[],
							[$root . $data['file']],
							'',
							$data['sender'] // $m['sender'] 
						);

						if ($sendEmail === true) 
						{
							echo "Send Email Success. <br>";
				
							$logging = $this->automail->logging(
							$projectId,
							'Message has been sent',
							null,
							$data['SO'],
							null,
							null,
							$data['INVOICE'],
							$data['file'],
							'File'
							);
				
							$this->automail->loggingEmail($logging, $data['to'], 1);
				
							$this->automail->initFolder($rootTemp, 'logs');
				
							$this->automail->moveFile($root, $rootTemp, 'logs/', $data['file']);
						} 
						else 
						{
							echo $sendEmail;
						} 

					}
				}
		
			}
			// if (count($fileFailed) !== 0) 
			// {
				
			// }
			// echo "<pre>".print_r($fileOkay,true)."</pre>";	
			// echo "<pre>".print_r($fileFailed,true)."</pre>";
    		// exit;


		} 
		catch (\Exception $e) 
		{
			echo $e->getMessage();
		}
	}

}
