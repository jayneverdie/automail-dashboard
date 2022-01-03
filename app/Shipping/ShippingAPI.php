<?php 

namespace App\Shipping;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class ShippingAPI {

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

	public function mapQuantationManyQA($customerCode, $quantation, $voucher) {
		try {
			return Database::hasRows(
				$this->db_ax,
				"SELECT 
				Q.custaccount, 
				Q.quotationid, 
				Q.DATAAREAID, 
				Q.CREATEDDATE, 
				CJ.VOUCHER_NO
				FROM SalesQuotationTable Q
				JOIN SALESTABLE SO
				ON SO.QUOTATIONID = Q.QUOTATIONID
				AND SO.DATAAREAID = Q.DATAAREAID
				JOIN CustPackingSlipJour CJ
				ON CJ.SALESID = SO.SALESID
				AND CJ.DATAAREAID = SO.DATAAREAID
				where Q.DATAAREAID = 'dsc'
				and Q.quotationid IN ($quantation)
				AND SO.CUSTACCOUNT = ?
				and CJ.VOUCHER_NO = ?
				order by Q.CREATEDDATE desc",
				[
					$customerCode, 
					$voucher
				]
			);
		} catch (\Exception $e) {
			return false;
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
			$txt .= '<b>Customer name : </b>'.$bodyData[0]['Customer'].'<br>';
			$txt .= '<b>PI ID : </b>DSC-'.$bodyData[0]['PI'].'<br>';
			$txt .= '<b>PO : </b>'.$bodyData[0]['PO'].'<br>';
			$txt .= '<b>SO ID : </b>'.$bodyData[0]['SO'].'<br>';
			$txt .= '<b>ETD : </b>'. self::isDateNull($bodyData[0]['ETD']) .'<br>';
			$txt .= '<b>ETA : </b>'.self::isDateNull($bodyData[0]['ETA']).'<br>';
			$txt .= '<b>Invoice No : </b>'.strtoupper($bodyData[0]['Company']) . '/' . $bodyData[0]['Year'] . '/' . substr($inv, 3).'<br>';
			$txt .= '<b>Destination port : </b>'.$bodyData[0]['ToPort'].'<br>';
			$txt .= '<b>Agent : </b>'. self::getAgent($bodyData[0]['SO'], $customerCode).  '<br>';
			$txt .= '<b>Shipping Line : </b>'. self::getShippingLine($bodyData[0]['SO'], $customerCode);

			return $txt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function isDateNull($date) {
		try {
			if (date('Y-m-d', strtotime($date)) === '1900-01-01' || 
					date('Y-m-d', strtotime($date)) === '1970-01-01' ) {
				return '-';
			} else {
				return date('d M Y', strtotime($date));
			}
		} catch (\Exception $e) {
			return '-';
		}
	}

	public function getShippingBodyData($inv, $customerCode) {
		try {
			$data = Database::rows(
				$this->db_ax,
				"SELECT 
				CT.NAME [Customer],
				ST.QUOTATIONID [PI],
				CC.CUSTOMERREF [PO],
				ST.SALESID [SO],
				CPS.DSG_ETD [ETD],
				CPS.DSG_ETA [ETA],
				ST.DSG_ToPortDesc [ToPort],
				CC.DSG_VOUCHERSERIES [Year],
				ST.DATAAREAID [Company]
				FROM SALESTABLE ST
				-- LEFT JOIN CustConfirmJour CC ON ST.CUSTACCOUNT = CC.INVOICEACCOUNT
				LEFT JOIN CustConfirmJour CC ON ST.SALESID = CC.SALESID AND CC.DATAAREAID = 'dsc'
				LEFT JOIN CUSTTABLE CT ON CT.ACCOUNTNUM = ST.CUSTACCOUNT AND CT.DATAAREAID = 'dsc'
				LEFT JOIN CustPackingSlipJour CPS ON ST.SALESID = CPS.SALESID AND CPS.DATAAREAID = 'dsc'
				WHERE ST.SALESID = CC.SALESID
				AND ST.CUSTACCOUNT = ?
				AND ST.DATAAREAID = 'dsc'
				AND CC.DSG_VoucherNo = ?
				AND CPS.SalesId = ST.SALESID
				ORDER BY CPS.PackingSlipId DESC",
				[
					$customerCode,
					substr($inv, 3)
				]
			);
	
			if (count($data) === 0) {
				return '';
			} else {
				return $data;
			}
		} catch (\Exception $e) {
			return '';
		}
	}

	public function getAgent($so, $customer) {
		try {
			$data = Database::rows(
				$this->db_ax,
				"SELECT A.DSG_DESCRIPTION [Agent]
				FROM Salestable ST
				LEFT JOIN DSG_AgentTable A ON A.DSG_AGENTID = ST.DSG_PRIMARYAGENTID AND A.DATAAREAID = 'dsc'
				where ST.salesid = ?
				AND ST.custaccount = ?
				AND ST.DATAAREAID = 'dsc'",
				[
					$so,
					$customer
				]
			);

			return $data[0]['Agent'];
		} catch (\Exception $e) {
			return '-';
		}
	}

	public function getShippingLine($so, $customer) {
		try {
			$res = Database::rows(
				$this->db_ax,
				"SELECT ST.DSG_ShippingLineDescription [ShippingLine]
				FROM Salestable ST
				where ST.salesid = ?
				AND ST.custaccount = ?
				AND ST.DATAAREAID = 'dsc'",
				[
					$so,
					$customer
				]
			);
	
			if ($res[0]['ShippingLine'] === '') {
				return '-';
			} 
			return $res[0]['ShippingLine'];
		} catch (\Exception $e) {
			return '-';
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

	public function getAOTSubject(array $fileName = [])
	{
		$files = '';
		foreach ($fileName as $name) {
			$files .= str_replace('DOCS', '', strtoupper(explode('.', $name)[0])) . ', ';
		}
		$files = trim($files, ', ');
		return 'NEW AOTC DOCS : ' . $files;
	}

	public function getAOTBody()
	{
		$text = '';
		$text .= 'Dear Sir / Madam,<br/><br/>';
		$text .= 'Please see shipping document as the attached. <br/><br/>';
		$text .= 'This e-mail is automatically generated. <br/> The information contained in this message is privileged and intended only for the recipients named. If the reader is not a representative of the intended recipient, any review, dissemination or copying of this message or the information it contains is prohibited. If you have received this message in error, please immediately notify the sender, and delete the original message and attachments.';
		return $text;
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

	public function isDocsAOTInFileName($files = []) {
		try {
			
			$filesWithDocs = [];
			foreach($files as $file) {
				if (preg_match("/DOCS/i", $file)) {
					$filesWithDocs[] = $file;
				}
			}

			return $filesWithDocs;

		} catch (\Exception $e) {
			return false;
		}
	}

	public function getMailFailed() {
		try {
			$res = [];
			$data = Database::rows(
				$this->db_live,
				"SELECT Email FROM EmailLists
				WHERE ProjectID = 5 -- shipping
				AND EmailType = 5 -- shipping fail
				AND [Status] = 1 -- active"
			);
			
			if (count($data) > 0) {
				foreach ($data as $v) {
					$res[] = $v['Email'];
				}
				return $res;
			} else {
				return [];
			}
		} catch (\Exception $e) {
			return [];
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
				WHERE ProjectID = 5 
				AND $filter
				ORDER BY ID DESC"
			);
			return $data;
		} catch (\Exception $e) {
			return [];
		}
	}

	public function getShippingConfirmSubject($file) {
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
			return "Shipping document [Please confirm] : " . $tempTxt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getShippingConfirmBody($file) {
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
			$txt .= 'Please see shipping document as attached. <br/>';
			$txt .= '<u><i><font color="green">Kindly review and confirm all document to us by return.</font></i></u>
				Awaiting for kind feedback. Thank you. <br/><br/>';
			$txt .= '<b>Customer name : </b>'.$bodyData[0]['Customer'].'<br>';
			$txt .= '<b>PI ID : </b>DSC-'.$bodyData[0]['PI'].'<br>';
			$txt .= '<b>PO : </b>'.$bodyData[0]['PO'].'<br>';
			$txt .= '<b>SO ID : </b>'.$bodyData[0]['SO'].'<br>';
			$txt .= '<b>ETD : </b>'. self::isDateNull($bodyData[0]['ETD']) .'<br>';
			$txt .= '<b>ETA : </b>'.self::isDateNull($bodyData[0]['ETA']).'<br>';
			$txt .= '<b>Invoice No : </b>'.strtoupper($bodyData[0]['Company']) . '/' . $bodyData[0]['Year'] . '/' . substr($inv, 3).'<br>';
			$txt .= '<b>Destination port : </b>'.$bodyData[0]['ToPort'].'<br>';
			$txt .= '<b>Agent : </b>'. self::getAgent($bodyData[0]['SO'], $customerCode).  '<br>';
			$txt .= '<b>Shipping Line : </b>'. self::getShippingLine($bodyData[0]['SO'], $customerCode);

			return $txt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getMailCustomer($projectId) {
		try {
			
			$listsTo = [];
			$listsCC = [];
			$listsInternal = [];
			$listsInternalCC = [];
			$listsSender = [];
			$listsFailed = [];
				
			$query = Database::rows(
				$this->db_live,
				"SELECT * FROM EmailLists WHERE ProjectID=? AND Status=?",[$projectId,1]
			);

			foreach($query as $q) {
				if ($q['EmailType']==1 && $q['EmailCategory']==16) {
					$listsTo[] = $q['Email'];
				}else if($q['EmailType']==2 && $q['EmailCategory']==16){
					$listsCC[] = $q['Email'];
				}else if($q['EmailType']==1 && $q['EmailCategory']==17){
					$listsInternal[] = $q['Email'];
				}else if($q['EmailType']==2 && $q['EmailCategory']==17){
					$listsInternalCC[] = $q['Email'];
				}else if($q['EmailType']==4 && $q['EmailCategory']==17){
					$listsSender[] = $q['Email'];
				}else if($q['EmailType']==5 && $q['EmailCategory']==17){
					$listsFailed[] = $q['Email'];
				}
			}

			return [
				'to' => $listsTo,
				'cc' => $listsCC,
				'internal' => $listsInternal,
				'internalcc' => $listsInternalCC,
				'sender' => $listsSender,
				'failed' => $listsFailed
			];

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function isAPI($file) {
		try {
			if (preg_match("/C2720/i", $file)) {
				return true;
			} else {
				return false;
			}
		} catch (\Exception $e) {
			throw new \Exception('Error: File in correct.');
		}
	}

	public function pathTofile($files = [], $root) {
		try {
			$file = [];
            for ($x=0; $x < count($files); $x++) { 
                $file[] = $root.$files[$x];
            }
            return $file;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function MapAgent($customerCode, $quantation, $voucher, $agent) {
		try {

			return Database::hasRows(
				$this->db_ax,
				"SELECT 
				Q.custaccount, 
				Q.quotationid, 
				Q.DATAAREAID, 
				Q.CREATEDDATE, 
				CJ.VOUCHER_NO,
				SO.DSG_PRIMARYAGENTID,
				SO.SALESID
				FROM SalesQuotationTable Q
				JOIN SALESTABLE SO
				ON SO.QUOTATIONID = Q.QUOTATIONID
				AND SO.DATAAREAID = Q.DATAAREAID
				JOIN CustPackingSlipJour CJ
				ON CJ.SALESID = SO.SALESID
				AND CJ.DATAAREAID = SO.DATAAREAID
				where Q.DATAAREAID = 'dsc'
				AND Q.quotationid IN ($quantation)
				AND SO.CUSTACCOUNT = ?
				AND CJ.VOUCHER_NO = ?
				AND SO.DSG_PRIMARYAGENTID LIKE '%$agent%'
				order by Q.CREATEDDATE desc",
				[
					$customerCode, 
					$voucher
				]
			);

		} catch (\Exception $e) {
			return '-';
		}
	}

	public function isFormatile($file) {
		try {
			if (preg_match("/C(?:....-)/i", $file) && preg_match("/QA(?:..-......)/i", $file)) {
				if (preg_match("/INV(?:....-)/i", $file) || preg_match("/INV.*?(\d+)/i", $file)) {
					return true;
				}else{
					return false;
				}
			} else {
				return false;
			}
		} catch (\Exception $e) {
			throw new \Exception('Error: File in correct.');
		}
	}

	public function getShippingSubjectConfirm($file) {
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
			return "Shipping document  [Please confirm] : " . $tempTxt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getShippingBodyConfirm($file) {
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
			$txt .= '<u><i><font color="green">Kindly review and confirm all document to us by return.</font></i></u>
				Awaiting for kind feedback. Thank you. <br/><br/>';
			$txt .= '<b>Customer name : </b>'.$bodyData[0]['Customer'].'<br>';
			$txt .= '<b>PI ID : </b>DSC-'.$bodyData[0]['PI'].'<br>';
			$txt .= '<b>PO : </b>'.$bodyData[0]['PO'].'<br>';
			$txt .= '<b>SO ID : </b>'.$bodyData[0]['SO'].'<br>';
			$txt .= '<b>ETD : </b>'. self::isDateNull($bodyData[0]['ETD']) .'<br>';
			$txt .= '<b>ETA : </b>'.self::isDateNull($bodyData[0]['ETA']).'<br>';
			$txt .= '<b>Invoice No : </b>'.strtoupper($bodyData[0]['Company']) . '/' . $bodyData[0]['Year'] . '/' . substr($inv, 3).'<br>';
			$txt .= '<b>Destination port : </b>'.$bodyData[0]['ToPort'].'<br>';
			$txt .= '<b>Agent : </b>'. self::getAgent($bodyData[0]['SO'], $customerCode).  '<br>';
			$txt .= '<b>Shipping Line : </b>'. self::getShippingLine($bodyData[0]['SO'], $customerCode);

			return $txt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}
}