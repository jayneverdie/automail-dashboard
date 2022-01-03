<?php

namespace App\TireGroup_shipping;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class TgsAPI {

	public function __construct() {
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
		$this->automail = new Automail;
	}

	public function isSurrender($file) {
		try {
			if (preg_match("/-SURRENDER/i", $file)) {
				return true;
			} else {
				return false;
			}
		} catch (\Exception $e) {
			throw new \Exception('Error: File in correct.');
		}
	}







	public function getLogs($filter) {
		try {
			$data = Database::rows(
				$this->db_live,
				"SELECT TOP 50
				[Message],
				CustomerCode,
				[FileName],
				SendDate
				FROM Logs
				WHERE ProjectID = 14
				AND $filter
				ORDER BY ID DESC"
			);
			return $data;
		} catch (\Exception $e) {
			return [];
		}
	}

	public function getShippingBody($file) {
		try {
			$txt = '';
			$inv = $this->automail->getInvoice($file);

			Assert::notEmpty($inv, 'Error: invoice not found.');
			Assert::notNull($inv, 'Error: invoice not found.');

			$customerCode = $this->automail->getCustomerCode($file);

			Assert::notEmpty($customerCode, 'Error: customer not found.');
			Assert::notNull($customerCode, 'Error: customer not found.');

			$bodyData = self::getShippingBodyData($inv, $customerCode);

			$txt .= 'Dear Sir / Madam,<br/><br/>';
			$txt .= 'Please see shipping document as attached. <br/><br/>';
			$txt .= '<b>Customer name : </b>'.$getData[0]['Customer'].'<br>';
			$txt .= '<b>PI ID : </b>DSC-'.$getData[0]['PI'].'<br>';
			$txt .= '<b>PO : </b>'.$getData[0]['PO'].'<br>';
			$txt .= '<b>SO ID : </b>'.$getData[0]['SO'].'<br>';
			$txt .= '<b>ETD : </b>'. self::isDateNull($getData[0]['ETD']) .'<br>';
			$txt .= '<b>ETA : </b>'.self::isDateNull($getData[0]['ETA']).'<br>';
			$txt .= '<b>Invoice No : </b>'.strtoupper($getData[0]['Company']) . '/' . $getData[0]['Year'] . '/' . substr($matched_inv[0], 3).'<br>';
			$txt .= '<b>Destination port : </b>'.$getData[0]['ToPort'].'<br>';
			$txt .= '<b>Agent : </b>'. self::getAgent($getData[0]['SO'], $_customerCode).  '<br>';
			$txt .= '<b>Shipping Line : </b>'. self::getShippingLine($getData[0]['SO'], $_customerCode);

			return $txt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getShippingSubject($file) {
		try {
			$tempTxt = '';
			$a = explode('.', $file);

			if (count($a) > 2) {
				for($i = 0; $i < count($a) - 1; $i++) {
					$tempTxt .= $a[$i];
				}
			} else {
				$tempTxt = $a[0];
			}
			return "Shipping document : " . $tempTxt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function isDocsInFileName($file) {
		try {
			if (preg_match("/-DOCS/i", $file)) {
				return true;
			} else {
				return false;
			}
		} catch (\Exception $e) {
			return false;
		}
	}
}
