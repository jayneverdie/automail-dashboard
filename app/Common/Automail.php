<?php

namespace App\Common;

use App\Common\Database;

class Automail
{
	private $db_ax = null;
	private $db_live = null;

	public function __construct()
	{
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
	}

	public function getDirRoot($path)
	{
		try {
			$rootDir = self::dirToArray($path);

			$files = [];

			foreach ($rootDir as $f) {
				if (\gettype($f) !== 'array') {
					if ($f !== 'Thumbs.db') {
						$files[] = $f;
					}
				}
			}
			return $files;
		} catch (\Exception $e) {
			throw new \Exception('Error: cannot get root path.');
		}
	}

	public function dirToArray($dir)
	{
		try {
			$result = array();
			$cdir = \scandir($dir);
			foreach ($cdir as $key => $value) {
				if (!\in_array($value, array(".", ".."))) {
					if (\is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
						$result[$value] = self::dirToArray($dir . DIRECTORY_SEPARATOR . $value);
					} else {
						$result[] = $value;
					}
				}
			}
			return $result;
		} catch (\Exception $e) {
			throw new \Exception('Error: cannot get dir path.');
		}
	}

	public function getCustomerCode($file)
	{
		try {
			preg_match('/C.*?(\\d+)/i', $file, $data);
			return substr_replace($data[0], '-', 1, 0);
		} catch (\Exception $e) {
			return '';
		}
	}

	public function getSOFromFileBooking($file)
	{
		try {
			preg_match_all('/SO(?:..-......)/i', $file, $data);
			if (count($data) === 0) {
				return [];
			} else {
				return $data[0][0];
			}
		} catch (\Exception $e) {
			return '';
		}
	}

	public function isBookingRevise($filename)
	{
		try {
			if (preg_match("/-REVI/i", $filename)) {
				return true;
			} else {
				return false;
			}
		} catch (\Exception $e) {
			return '';
		}
	}

	public function getQuantation($file)
	{
		try {
			preg_match('/QA(?:..-......)/i', $file, $data);

			if (count($data) === 0) {
				return [];
			} else {
				return $data[0];
			}
		} catch (\Exception $e) {
			return [];
		}
	}

	public function getQuantationArray($file)
	{
		try {
			preg_match_all('/QA(?:..-......)/i', $file, $data);

			if (count($data) === 0) {
				return [];
			} else {
				return $data[0];
			}
		} catch (\Exception $e) {
			return [];
		}
	}

	public function convertArrayToInSQL($arr)
	{
		try {
			$txt = '';
			if (is_array($arr)) {
				foreach ($arr as $v) {
					$txt .= '\'' . $v . '\',';
				}
				return trim($txt, ',');
			} else {
				return '';
			}
		} catch (\Exception $e) {
			return '';
		}
	}

	public function getInvoice($file)
	{
		try {
			preg_match('/INV.*?(\d+)/i', $file, $data);

			if (count($data) === 0) {
				return [];
			} else {
				return $data[0];
			}
		} catch (\Exception $e) {
			return [];
		}
	}

	public function Size($path)
	{
		$bytes = sprintf('%u', filesize($path));

		if ($bytes > 0) {
			$unit = intval(log($bytes, 1024));
			$units = array('B', 'KB', 'MB', 'GB');

			if (array_key_exists($unit, $units) === true) {
				return sprintf('%.2f %s', $bytes / pow(1024, $unit), $units[$unit]);
			}
		}

		return $bytes;
	}

	public function getCustomerMail($customerCode)
	{
		try {
			$listsTo = [];
			$listsCC = [];

			$_to = Database::rows(
				$this->db_ax,
				"SELECT DSG_Email
				FROM DSG_CustomerEmailList
				WHERE DSG_ACCOUNTNUM = ?
				AND DSG_SENDTYPE = 0
				AND DSG_INACTIVE = 0
				AND DATAAREAID = 'dsc'
				GROUP BY DSG_Email",
				[
					$customerCode
				]
			);

			$_cc = Database::rows(
				$this->db_ax,
				"SELECT DSG_Email
				FROM DSG_CustomerEmailList
				WHERE DSG_ACCOUNTNUM = ?
				AND DSG_SENDTYPE = 1
				AND DSG_INACTIVE = 0
				AND DATAAREAID = 'dsc'
				GROUP BY DSG_Email",
				[
					$customerCode
				]
			);

			if (count($_to) > 0) {
				foreach ($_to as $t) {
					$listsTo[] = $t['DSG_Email'];
				}
			} else {
				return ['to' => [], 'cc' => []];
			}

			if (count($_cc) > 0) {
				foreach ($_cc as $c) {
					$listsCC[] = $c['DSG_Email'];
				}
			}

			return [
				'to' => $listsTo,
				'cc' => $listsCC
			];
		} catch (\Exception $e) {
			return ['to' => [], 'cc' => []];
		}
	}

	public function getEmailFromCustomerCode($customerCode)
	{
		try {
			$data = Database::rows(
				$this->db_ax,
				"SELECT DSG_EMAIL
				FROM DSG_CUSTOMEREMAILLIST
				WHERE DSG_ACCOUNTNUM = ?
				AND DSG_SENDTYPE = 3 -- sender
				AND DSG_INACTIVE = 0",
				[
					$customerCode
				]
			);

			if (count($data) === 0) {
				return [];
			} else {
				return $data[0]['DSG_EMAIL'];
			}
		} catch (\Exception $e) {
			return [];
		}
	}

	public function initFolder($root, $folder)
	{
		try {
			$dateFolder = date('Y') . date('m');

			if (!file_exists($root . '/' . $folder . '/' . $dateFolder . '/')) {
				mkdir($root . '/' . $folder . '/' . $dateFolder . '/', 0777, true);
			}

			return "Create folder success.\n";
		} catch (\Exception $e) {
			throw new \Exception('Error: create folder failed.');
		}
	}

	public function moveFile($root, $rootTemp, $folder, $file)
	{
		try {
			$dateFolder = date('Y') . date('m') . '/';
			$file_ = explode('.', $file);

			if (count($file_)==2) {
				rename(
					$root . $file,
					$rootTemp . $folder . $dateFolder . $file_[0] . '_' . date('Ymd-His') . '.' . $file_[1]
				);
			}else{
				rename(
					$root . $file,
					$rootTemp . $folder . $dateFolder . $file
				);
			}

			return "Move file success.\n";
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getSubjectReportFailed($title = '')
	{
		$title = 'Automail: รายชื่อไฟล์ที่ไม่สามารถส่งหาลูกค้าได้';
		return $title;
	}

	public function getBodyReportFailed($file, $name = '', $note = '')
	{
		$txt = '';
		$txt .= 'รายชื่อไฟล์ที่ ' . $name . ' ไม่สามารถส่งให้ลูกค้าได้ รายละเอียดตามไฟล์แนบ<br><br>';

		if ($note !== '') {
			$txt .= 'สาเหตุ : ' . $note . '<br>';
		}

		$txt .= '<ul>';

		foreach ($file as $f) {
			$txt .= '<li>' . $f . '</li>';
		}
		$txt .= '</ul><br>';
		$txt .= '';
		return $txt;
	}

	public function getBodyReportInternalFailed($file, $name = '', $note = '')
	{
		$txt = '';
		$txt .= 'รายชื่อไฟล์ ' . $name . ' ไม่สามารถส่งได้ รายละเอียดตามไฟล์แนบ<br><br>';

		if ($note !== '') {
			$txt .= 'สาเหตุ : ' . $note . '<br>';
		}

		$txt .= '<ul>';

		foreach ($file as $f) {
			$txt .= '<li>' . $f . '</li>';
		}
		$txt .= '</ul><br>';
		$txt .= '';
		return $txt;
	}

	public function updateFilePath($root, $files)
	{
		try {
			$newLists = [];
			foreach ($files as $file) {
				$newLists[] = $root . $file;
			}
			return $newLists;
		} catch (\Exception $e) {
			return [];
		}
	}

	public function logging($projectId, $message, $customerCode, $so, $pi, $qa, $invoice, $filename, $source)
	{
		try {
			$logging = Database::query(
				$this->db_live,
				"INSERT INTO Logs(
					ProjectID,
					[Message],
					CustomerCode,
					SO,
					[PI],
					QA,
					Invoice,
					[FileName],
					Source,
					SendDate
				) VALUES(
					?, ?, ?, ?, ?,
					?, ?, ?, ?, ?
				); SELECT scope_identity() as TempId",
				[
					$projectId,
					$message,
					$customerCode,
					$so,
					$pi,
					$qa,
					$invoice,
					$filename,
					$source,
					date('Y-m-d H:i:s')
				]
			);

			if ($logging) {
				sqlsrv_next_result($logging);
				$row = sqlsrv_fetch_array($logging);
				return $row['TempId'];
			} else {
				throw new \Exception('Save log failed.');
			}
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function loggingEmail($tempId, $email = [], $emailtype)
	{
		try {

			if (count($email) > 0) {

				foreach ($email as $e) {

					$logging = Database::query(
						$this->db_live,
						"INSERT INTO LogsSendMail(
							LogsID,
							Email,
							EmailType,
							SendDate
						) VALUES (
							?, ?, ?, ?
						)",
						[
							$tempId,
							$e,
							$emailtype,
							date('Y-m-d H:i:s')
						]
					);
				}
			}
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function checkCustomerFilename($projectId,$filename)
	{
		try {
			if($projectId==42){
				if (preg_match("/C2720/i", $filename)) {
					return true;
				} else {
					return false;
				}
			}else if($projectId==14 || $projectId==17 || $projectId==18){
				if (preg_match("/C1089/i", $filename)) {
					return true;
				} else {
					return false;
				}
			}else if($projectId==7){
				if (preg_match("/C1441/i", $filename)) {
					return true;
				} else {
					return false;
				}
			}else if($projectId==8 || $projectId==9){
				if (preg_match("/C2536/i", $filename)) {
					return true;
				} else {
					return false;
				}
			}else {
				return false;
			}

		} catch (\Exception $e) {
			return '';
		}
	}
}
