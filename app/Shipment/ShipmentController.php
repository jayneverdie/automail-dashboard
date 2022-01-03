<?php

namespace App\Shipment;

use App\Common\View;
use App\Shipment\ShipmentAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ShipmentController {

	public function __construct() {
		$this->view = new View;
		$this->api = new ShipmentAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}
	public function sendmaildaily($request, $response, $args)
    {
         try {			

			$date = date("d.m.Y");
			$projectId = 52;
			$root = 'D:\automail\Shipment_API\\';

			$files = $this->automail->getDirRoot($root);
			$getMail = $this->api->getMail($projectId);

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
                        $allFiles[] = [
                            'file_name' => $file
                        ];
                    }
                }
            }

			sort($allFiles);

			// echo "<pre>".print_r($getMail,true)."</pre>";
			// echo "<pre>".print_r($allFiles,true)."</pre>";
   			// exit;

			foreach ($allFiles as $file) 
			{
				if ($file['file_name'] === "Shipment API.xls") 
				{
					$subject = $this->api->getSubject($date);
                    $body = $this->api->getBody();

					// echo "<pre>"."sender :".print_r($getMail['sender'],true)."</pre>";
					// echo "<pre>"."to External :".print_r($getMail['toExternal'],true)."</pre>
     				// echo "<pre>"."to Internal :".print_r($getMail['toInternal'],true)."</pre>
					// echo "<pre>".print_r($root . $a['file_name'],true)."</pre>";
					// echo "<pre>".print_r($subject,true)."</pre>";
					// echo "<pre>".print_r($body,true)."</pre>";
					// exit;
				
					$sendEmailExternal = $this->email->sendEmail(
						$subject,
						$body,
						$getMail['toExternal'],
						[],
						[],
						[$root . $file['file_name']],
						'',
						$getMail['sender']
					);
				
					if ($sendEmailExternal == true) 
					{
                        echo "<pre>".print_r("Message has been sent to External !!",true)."</pre>";

						$sendEmailInternal = $this->email->sendEmail(
							$subject,
							$body,
							$getMail['toInternal'],
							[],
							[],
							[$root . $file['file_name']],
							'',
							$getMail['sender']
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

						$this->automail->loggingEmail($logging,$getMail['toExternal'],1); //To

						if ($sendEmailInternal == true) 
                        {
							$this->automail->loggingEmail($logging,$getMail['toInternal'],1);
                            echo "<pre>".print_r("Message has been sent to Internal !!",true)."</pre>";

							$this->automail->initFolder($root, 'temp');
							$this->automail->moveFile($root, $root, 'temp/', $file['file_name']);	
						}
						else
						{
							echo $sendEmail;
							// // sendfailed movefile
							$this->automail->initFolder($root, 'failed');
							$this->automail->moveFile($root, $root, 'failed/',$file['file_name']);	
						}
						
					}
					else
					{
						 echo $sendEmail;
						// // sendfailed movefile
						$this->automail->initFolder($root, 'failed');
						$this->automail->moveFile($root, $root, 'failed/',$file['file_name']);
					}
				}
				// exit;
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}