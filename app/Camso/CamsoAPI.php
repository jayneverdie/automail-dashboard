<?php 

namespace App\Camso;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class CamsoAPI {

	public function __construct() {
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
		$this->automail = new Automail;
	}

	public function getCamsoFullQuantation($fileName) {
		try {

			preg_match('/QA(?:..-......)(?:#.)/i', $fileName, $matched);
			
			if (count($matched) > 0) {
				return 'DSC-' . $matched[0];
			} else {
				preg_match('/QA(?:..-......)/i', $fileName, $matchedNoSharp);
				return 'DSC-' . $matchedNoSharp[0];
			}

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getDataFromInvoice($filename) {
		try {
			
			preg_match('/INV.*?(\\d+)/i', $filename, $matched_inv);
			preg_match('/C.*?(\\d+)/i', $filename, $matched_cust);

			$inv =  substr($matched_inv[0], 3);

			$rows = Database::rows(
				$this->db_ax,
				"SELECT 
				CCJ.DSG_VOUCHERNO, 
				CCJ.DSG_VOUCHERSERIES, 
				CCJ.DSG_LC_NO,
				CCJ.DATAAREAID,
				LC.DSG_ISSUEDBANK,
				CCJ.DSG_SHIPPINGMARK,
				CCJ.CUSTOMERREF,
				LC.DSG_Applicant,
				LC.DSG_LC_DATE1
				FROM CustConfirmJour CCJ
				LEFT JOIN DSG_LCTable LC ON LC.DSG_LC_NO1 = CCJ.DSG_LC_NO
				WHERE CCJ.DATAAREAID = 'dsc'
				AND CCJ.DSG_VOUCHERNO <> 0
				AND CCJ.DSG_NOYESID_DISABLED <> 1
				AND CCJ.DSG_VOUCHERNO = ?
				AND CCJ.INVOICEACCOUNT = ?",
				[
					$inv,
					substr_replace($matched_cust[0], '-', 1, 0)
				]
			);

			if (!$rows) {
				return [];
			}

			if (count($rows) !== 0) {
				return $rows[0];
			} else {
				return [];
			}

		} catch (\Exception $e) {
			return false;
		}
	}

	public function newLineShippingMark($str) {
		try {
			return str_replace("CAMSO LOADSTAR,", "<br/>CAMSO LOADSTAR,<br/>", $str);
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getQaNoSharp($fileName) {
		try {
			preg_match('/QA(?:..-......)/i', $fileName, $matchedNoSharp);
			return 'DSC-' . $matchedNoSharp[0];
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getStateAndPort($qa, $cust) {
		try {
			$sql = "SELECT 
				ST.QUOTATIONID,
				ST.CUSTACCOUNT,
				DC.COUNTRY [Country],
				DC.DSG_STATE,
				ST.DSG_ToPortId [Port],
				DC.DESTINATIONCODEID
				from Salestable ST
				left join DESTINATIONCODE DC 
				ON ST.DSG_TOPORTID = DC.DESTINATIONCODEID
				where ST.DSG_ToPortId <> ''
				and DC.DATAAREAID = 'dsc'
				and ST.DATAAREAID = 'dsc'
				and ST.QUOTATIONID = ?
				and ST.CUSTACCOUNT = ?
				group by 
				ST.QUOTATIONID,
				ST.CUSTACCOUNT,
				DC.COUNTRY,
				DC.DSG_STATE,
				ST.DSG_ToPortId,
				DC.DESTINATIONCODEID";

			$query = Database::rows(
				$this->db_ax,
				$sql,
				[
					substr($qa, 4),
					$cust
				]
			);

			if ( count($query) === 0 ) {
				return [];
			}

			return $query;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getCustomerCode($file) {
		try {
			preg_match('/C.*?(\\d+)/i', $file, $data);
			return substr_replace($data[0], '-', 1, 0);
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function camsoStateActive($file_state, $custcode) {
		try {
			$state['C-1441'] = ["SLK"];

			$state['C-2536'] = [
				"CAN",
				"EGY",
				"FRA",
				"DEU",
				"MEX",
				"POL",
				"TUR",
				"VNM",
				"USA",
				"GBR"
			];

			if ( array_key_exists($custcode, $state) === false ) {
				return false;
			} 

			if ( in_array($file_state, $state[$custcode]) === false ) {
				return false;
			}

			return true;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getMailCustomer($projectId, $state, $port) {
		try {
			
			$listsTo = [];
			$listsCC = [];
			$listsInternal = [];
			$listsSender = [];

			if ($projectId == 7) {
				
				$query = Database::rows(
					$this->db_live,
					"SELECT * FROM EmailLists WHERE ProjectID=7"
				);

				foreach($query as $q) {
					if ($q['EmailType']==1 && $q['EmailCategory']==16) {
						$listsTo[] = $q['Email'];
					}else if($q['EmailType']==2 && $q['EmailCategory']==16){
						$listsCC[] = $q['Email'];
					}else if($q['EmailType']==1 && $q['EmailCategory']==17){
						$listsInternal[] = $q['Email'];
					}else if($q['EmailType']==4 && $q['EmailCategory']==16){
						$listsSender[] = $q['Email'];
					}
				}

				return [
					'to' => $listsTo,
					'cc' => $listsCC,
					'internal' => $listsInternal,
					'sender' => $listsSender
				];

			}else if($projectId == 8){

				if ($state=='USA') {

					$query = Database::rows(
						$this->db_live,
						"SELECT * FROM EmailLists WHERE ProjectID=8 AND Country=? AND Port=?",
						[$state,$port]
					);

				}else{

					$query = Database::rows(
						$this->db_live,
						"SELECT * FROM EmailLists WHERE ProjectID=8 AND Country=?",
						[$state]
					);

				}

					$sender = Database::rows(
						$this->db_live,
						"SELECT * FROM EmailLists WHERE ProjectID=7 AND EmailType=4"
					);

					foreach ($sender as $s) {
						$listsSender[] = $s['Email'];
					}

					foreach($query as $q) {
						if ($q['EmailType']==1 && $q['EmailCategory']==16) {
							$listsTo[] = $q['Email'];
						}else if($q['EmailType']==2 && $q['EmailCategory']==16){
							$listsCC[] = $q['Email'];
						}else if($q['EmailType']==1 && $q['EmailCategory']==17){
							$listsInternal[] = $q['Email'];
						}
					}

				return [
					'to' => $listsTo,
					'cc' => $listsCC,
					'internal' => $listsInternal,
					'sender' => $listsSender
				];


			}else if($projectId == 9){

				$query = Database::rows(
					$this->db_live,
					"SELECT * FROM EmailLists WHERE ProjectID=9"
				);

				foreach($query as $q) {
					if ($q['EmailType']==1 && $q['EmailCategory']==16) {
						$listsTo[] = $q['Email'];
					}else if($q['EmailType']==2 && $q['EmailCategory']==16){
						$listsCC[] = $q['Email'];
					}else if($q['EmailType']==1 && $q['EmailCategory']==17){
						$listsInternal[] = $q['Email'];
					}else if($q['EmailType']==4 && $q['EmailCategory']==16){
						$listsSender[] = $q['Email'];
					}
				}

				return [
					'to' => $listsTo,
					'cc' => $listsCC,
					'internal' => $listsInternal,
					'sender' => $listsSender
				];

			}

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getCamsoSubject($qa, $customer, $camso_type) {
		try {
			if ($camso_type === 'ISF') { // ISF
				return 'ISF DOCUMENT: ' . $customer . ': P/I NO. '. $qa;
			} else {
				return 'COPY OF EACH OF THE DOCUMENT: ' . $customer . ': P/I NO. '. $qa;
			}
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getCamsoBody($po, $ref, $inv_no, $lc_no, $remark, $issue, $date, $customer, $camso_type) {
		try {
			$text = '';
			$text .= 'Dear Sir / Madam,<br/><br/>';
			if ($camso_type === 'ISF') { // ISF
				$text .= 'ISF DOCUMENTS: ' .$customer. ': P/I NO. '. $ref .'<br/>';
			} else {
				$text .= 'COPY OF EACH OF THE DOCUMENTS: ' .$customer. ': P/I NO. '. $ref .'<br/>';
			}
			
			$text .= "<br><b>PO # </b>" . str_replace('PO #', '', $po).'<br/>';
			$text .= '<b>INV.NO. </b>'.$inv_no. '<br/><br/>';
			$text .= '<b>L/C NO. </b>'.$lc_no.' <b>DATE</b> ' . date('d/m/Y', strtotime($date)) .'<br/>';
			$text .= '<b>ISSUING BANK </b>'.$issue.'<br/><br/>';
			
			if ($remark !== '' && $camso_type !== 1) {
				$text .= '<b>SHIPPING MARKS AND NOS. AS : </b> <br>'. $remark;
			}else{
				$text .= '';
			}

			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function isDocsInFileName($files=[]) {
		try {
			$filesWithDocs = [];
			foreach($files as $file) {
				if (preg_match("/-DOCS/i", $file)) {
					$filesWithDocs[] = $file;
				}
			}
			return $filesWithDocs;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function checkFileExist($file,$projectId) {
		try {
			return Database::hasRows(
				$this->db_live,
				"SELECT *
				FROM Logs
				WHERE FileName = ? AND ProjectID = ?",
				[
					$file,$projectId
				]
			);
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

	public function getMailCustomerWeekly($projectId) {
		try {

			$listsTo = [];
			$listsCC = [];
			$listsInternal = [];
			$listsSender = [];

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
				}else if($q['EmailType']==4 && $q['EmailCategory']==17){
					$listsSender[] = $q['Email'];
				}
			}

			return [
				'to' => $listsTo,
				'cc' => $listsCC,
				'internal' => $listsInternal,
				'sender' => $listsSender
			];

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

}