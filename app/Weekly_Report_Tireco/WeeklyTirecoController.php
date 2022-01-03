<?php

namespace App\Weekly_Report_Tireco;

use App\Common\View;
use App\Weekly_Report_Tireco\WeeklyTirecoAPI;
use App\Common\Automail;
use App\Email\EmailAPI;
use App\Common\Datatables;
use PHPExcel;
use PHPExcel_IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class WeeklyTirecoController
{

	public function __construct()
	{
		$this->view = new View;
		$this->api = new WeeklyTirecoAPI;
		$this->automail = new Automail;
		$this->email = new EmailAPI;
		$this->datatables = new Datatables;
	}
	public function sendmail($request, $response, $args)
	{
		try {

			$projectId = 53;
			//$root = 'D:\automail\\Shipment_Plan_Camso\\';
			$root = 'D:\automail\Weekly_Tireco_Greenball\\';
			// $name = "Weekly Report Tire group and Atturo.xls";
			$files = $this->automail->getDirRoot($root);
			$getMail = $this->api->getMail($projectId);
			// echo "<pre>" . print_r($file, true) . "</pre>";
			// exit();

			$date2 = date("Y-m-d");
			$date = date_create($date2);
			$datecheck = $date->format('m.d.Y');
			$body = $this->api->getBody();
			// echo $body;
			// exit();
			// echo "<pre>" . print_r($getMail, true) . "</pre>";

			// exit();


			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel.php';
			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';



			$inputfile  = $root . $files[0];
			$inputfile2  = $root . $files[1];



			$filetype = PHPExcel_IOFactory::identify($inputfile);

			$excel = PHPExcel_IOFactory::createReader($filetype);
			$excel = $excel->load($inputfile);

			$excel->setActiveSheetIndex(0);
			$excel->getActiveSheet()->setCellValue('A1', 'Weekly report of Greenball');
			$objWriter = PHPExcel_IOFactory::createWriter($excel, $filetype);
			$objWriter->save($inputfile);

			// File2
			$filetype2 = PHPExcel_IOFactory::identify($inputfile2);

			$excel2 = PHPExcel_IOFactory::createReader($filetype2);
			$excel2 = $excel2->load($inputfile);

			$excel2->setActiveSheetIndex(0);
			$excel2->getActiveSheet()->setCellValue('A1', 'Weekly report of Greenball');
			$objWriter = PHPExcel_IOFactory::createWriter($excel2, $filetype2);
			$objWriter->save($inputfile2);
			// echo "<pre>" . print_r($files, true) . "</pre>";

			// exit();


			// exit();
			foreach ($files as $file) {
				if (gettype($file) !== 'array') {
					if ($file !== 'Thumbs.db') {

						$allFiles[] = [
							'file_name' => $file
						];
					}
				}
			}
			sort($allFiles);
			// echo "<pre>" . print_r($allFiles[1], true) . "</pre>";
			$file1 = $allFiles[0];
			$file2 = $allFiles[1];
			$file1 = implode(" ", $file1);
			$file2 = implode(" ", $file2);
			//exit;
			//s	foreach ($allFiles as $file) {
			//if ($file['file_name'] === "Shipment Plan_Camso Trading (Private) Limited.xls") {
			$subject = $this->api->getSubject($datecheck);
			$body = $this->api->getBody();



			$sendEmailExternal = $this->email->sendEmail(
				$subject,
				$body,
				$getMail['toExternal'],
				$getMail['ccExternal'],
				[],
				[$root . $file1, $root . $file2],
				'',
				$getMail['sender']
			);

			if ($sendEmailExternal == true) {
				echo "<pre>" . print_r("Message has been sent to External !!", true) . "</pre>";

				$sendEmailInternal = $this->email->sendEmail(
					$subject,
					$body,
					$getMail['toInternal'],
					[],
					[],
					[$root . $file1, $root . $file2],
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

				$this->automail->loggingEmail($logging, $getMail['toExternal'], 1); //1To
				$this->automail->loggingEmail($logging, $getMail['ccExternal'], 2); //cc

				if ($sendEmailInternal == true) {

					echo "<pre>" . print_r("Message has been sent to Internal !!", true) . "</pre>";
				}
				$this->automail->initFolder($root, 'temp');
				$this->automail->moveFile($root, $root, 'temp/', $file['file_name']);
			} else {
				//echo $sendEmail;
				//	sendfailed movefile

				$this->automail->initFolder($root, 'failed');
				$this->automail->moveFile($root, $root, 'failed/', $file['file_name']);
			}
			//	}
			//	}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}
	public function sendmailTireco($request, $response, $args)
	{
		try {

			$projectId = 55;
			//$root = 'D:\automail\\Shipment_Plan_Camso\\';
			$root = 'D:\automail\Weekly_Tireco_Greenball\Tireco\\';
			// $name = "Weekly Report Tire group and Atturo.xls";
			$files = $this->automail->getDirRoot($root);
			$getMail = $this->api->getMail($projectId);
			// echo "<pre>" . print_r($files, true) . "</pre>";
			// exit();

			$date2 = date("Y-m-d");
			$date = date_create($date2);
			$datecheck = $date->format('m.d.Y');
			$body = $this->api->getBody();
			// echo $body;
			// exit();
			// echo "<pre>" . print_r($getMail, true) . "</pre>";

			// exit();


			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel.php';
			require_once '../vendor/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';



			$inputfile  = $root . $files[0];
			$inputfile2  = $root . $files[1];



			$filetype = PHPExcel_IOFactory::identify($inputfile);

			$excel = PHPExcel_IOFactory::createReader($filetype);
			$excel = $excel->load($inputfile);

			$excel->setActiveSheetIndex(0);
			$excel->getActiveSheet()->setCellValue('A1', 'Weekly report of Tireco');
			$objWriter = PHPExcel_IOFactory::createWriter($excel, $filetype);
			$objWriter->save($inputfile);

			// File2
			$filetype2 = PHPExcel_IOFactory::identify($inputfile2);

			$excel2 = PHPExcel_IOFactory::createReader($filetype2);
			$excel2 = $excel2->load($inputfile);

			$excel2->setActiveSheetIndex(0);
			$excel2->getActiveSheet()->setCellValue('A1', 'Weekly report of Tireco');
			$objWriter = PHPExcel_IOFactory::createWriter($excel2, $filetype2);
			$objWriter->save($inputfile2);
			// echo "<pre>" . print_r($files, true) . "</pre>";

			// exit();


			// exit();
			foreach ($files as $file) {
				if (gettype($file) !== 'array') {
					if ($file !== 'Thumbs.db') {

						$allFiles[] = [
							'file_name' => $file
						];
					}
				}
			}
			sort($allFiles);
			// echo "<pre>" . print_r($allFiles, true) . "</pre>";
			// exit();
			$file1 = $allFiles[0];
			$file2 = $allFiles[1];
			$file1 = implode(" ", $file1);
			$file2 = implode(" ", $file2);
			//exit;
			//s	foreach ($allFiles as $file) {
			//if ($file['file_name'] === "Shipment Plan_Camso Trading (Private) Limited.xls") {
			$subject = $this->api->getSubjectTireco($datecheck);
			$body = $this->api->getBodyTireco();



			$sendEmailExternal = $this->email->sendEmail(
				$subject,
				$body,
				$getMail['toExternal'],
				$getMail['ccExternal'],
				[],
				[$root . $file1, $root . $file2],
				'',
				$getMail['sender']
			);

			if ($sendEmailExternal == true) {
				echo "<pre>" . print_r("Message has been sent to External !!", true) . "</pre>";

				$sendEmailInternal = $this->email->sendEmail(
					$subject,
					$body,
					$getMail['toInternal'],
					[],
					[],
					[$root . $file1, $root . $file2],
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
					$file1." & ".$file2,
					'File'
				);

				$this->automail->loggingEmail($logging, $getMail['toExternal'], 1); //1To
				$this->automail->loggingEmail($logging, $getMail['ccExternal'], 2); //cc

				if ($sendEmailInternal == true) {

					echo "<pre>" . print_r("Message has been sent to Internal !!", true) . "</pre>";
				}

				$this->automail->initFolder($root, 'temp');
				foreach ($allFiles as $file) {
					$this->automail->moveFile($root, $root, 'temp/', $file['file_name']);
				}
				
			} else {
				//echo $sendEmail;
				//	sendfailed movefile

				$this->automail->initFolder($root, 'failed');
				foreach ($allFiles as $file) {
					$this->automail->moveFile($root, $root, 'failed/', $file['file_name']);
				}
			}
			//	}
			//	}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}
}
