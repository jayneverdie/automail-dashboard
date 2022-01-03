<?php

namespace App\Booking;

use App\Common\View;
use App\Booking\BookingAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;

class BookingController {

	public function __construct() {
		$this->view = new View;
		$this->booking = new BookingAPI;
		$this->automail = new Automail;
		$this->emailApi = new EmailAPI;
		$this->datatables = new Datatables;
	}

	public function send($request, $response, $args) 
	{
		try {

			$projectId = 10;
			$root = 'files/booking_confirmation/all/';
			$rootTemp = 'temp/booking_confirmation/all/';
			$filesOkay = [];
			$fileFailed = [];

			// $this->automail->initFolder($root);
			$files = $this->automail->getDirRoot($root);
			$email = $this->booking->getEmail($projectId);
			
			foreach ($files as $file) 
			{
				$so = $this->automail->getSOFromFileBooking($file);
				$customer = $this->automail->getCustomerCode($file);

				if ($this->automail->isBookingRevise($file) === false) {

					if (isset($so) && $this->booking->isSOAndCustomerMatched($so, $customer) === true) {
						$customerCode = $this->automail->getCustomerCode($file);
						$fileOkay[] = [
							'customer' => $customerCode,
							'file' => $file,
							'so' => $so
						];
					} 
					else 
					{
						$fileFailed[] = $file;
					}

				} 

			}
			
			if (count($fileOkay) > 0) 
			{

				foreach ($fileOkay as $data) {


					$success[] = [
						'customer' => $data['customer'],
						'so' => $data['so'],
						'email' => $email,
						'file' => $data['file'],
						'sender' => $this->automail->getEmailFromCustomerCode($data['customer']),
					];
				}

				if ($email === null) 
				{
					throw new \Exception("No email to send.");
				}

				foreach ($success as $m) 
				{
					if ($this->booking->checkFileExist($m['file'],$projectId)===false)
					{
						$subject = $this->booking->getBookingSubject_v2($m['file']);
						$body = $this->booking->getBookingBody_v2($m['file']);

						$sendEmailInternal = $this->emailApi->sendEmail(
							$subject,
							$body,
							$m['email']['to'],
							$m['email']['cc'],
							[],
							[$root . $m['file']],
							'',
							$m['sender'] // $m['sender'] 
						);
				
						if ($sendEmailInternal === true) 
						{
							echo "Send Email Success. <br>";
				
							$logging = $this->automail->logging(
							$projectId,
							'Message has been sent',
							$m['customer'],
							null,
							null,
							null,
							null,
							$m['file'],
							'File'
							);
				
							$this->automail->loggingEmail($logging, $email['to'], 1);
				
							$this->automail->initFolder($rootTemp, 'logs');
				
							$this->automail->moveFile($root, $rootTemp, 'logs/', $m['file']);
						} 
						else 
						{
							echo $sendEmailInternal;
						} 
					}
					
				}
			}

			if (count($fileFailed) > 0) 
			{
					// $this->automail->initFolder($rootTemp, 'failed');
					$this->automail->moveFile($root, $rootTemp, 'failed/', $m['file']);
			}

		} 
		catch (\Exception $e) 
		{
			echo $e->getMessage();
		}
	}

}
