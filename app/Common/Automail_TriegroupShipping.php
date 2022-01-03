<?php

namespace App\Common;

use App\Common\Database;

class Automail_TriegroupShipping {

	public function __construct() {
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
	}

	public function getDirRoot($path) {
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

	public function dirToArray($dir) {
		try {
			$result = array();
			$cdir = \scandir($dir);
			foreach ($cdir as $key => $value) {
				if (!\in_array($value,array(".",".."))) {
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

	public function getCustomerCode($file) {
		try {
			preg_match('/C.*?(\\d+)/i', $file, $data);
			return substr_replace($data[0], '-', 1, 0);
		} catch (\Exception $e) {
			return '';
		}
	}

	public function getQuantationArray($file) {
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

	public function convertArrayToInSQL($arr) {
		try {
			$txt = '';
			if(is_array($arr)) {
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

	public function getInvoice($file) {
		try {
			preg_match('/INV.*?(\\d+)/i', $file, $data);

			if (count($data) === 0) {
				return [];
			} else {
				return $data[0];
			}
		} catch (\Exception $e) {
			return [];
		}
	}

	public function getCustomerMail($customerCode) {
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
				foreach($_to as $t) {
					$listsTo[] = $t['DSG_Email'];
				}
			}	else {
				return ['to' => [], 'cc' => []];
			}

			if (count($_cc) > 0) {
				foreach($_cc as $c) {
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

	public function getEmailFromCustomerCode($customerCode) {
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

	public function initFolder($root) {
		try {
			$dateFolder = date('Y') . date('m');

			if (!file_exists($root . '/failed/'. $dateFolder . '/')) {
				mkdir($root . '/failed/'. $dateFolder . '/', 0777, true);
			}

			if (!file_exists($root . '/temp/'. $dateFolder . '/')) {
				mkdir($root . '/temp/'. $dateFolder . '/', 0777, true);
			}

			return 'Create folder success.';
		} catch (\Exception $e) {
			throw new \Exception('Error: create folder failed.');
		}
	}

	public function moveFile($root, $folder,  $file) {
		try {
			$dateFolder = date('Y') . date('m') . '/';
			rename(
				$root . $file,
				$root . $folder . $dateFolder . $file . '_' . date('Ymd-His')
			);

			return 'Move file success.';
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getSubjectReportFailed($title = '') {
		$title = 'Automail: รายชื่อไฟล์ที่ไม่สามารถส่งหาลูกค้าได้';
		return $title;
	}

	public function getBodyReportFailed($file, $name = '', $note = '') {
		$txt = '';
		$txt .= 'รายชื่อไฟล์ที่ ' . $name . ' ไม่สามารถส่งให้ลูกค้าได้ รายละเอียดตามไฟล์แนบ<br><br>';

		if ($note !== '') {
			$txt .= 'สาเหตุ : ' . $note . '<br>';
		}

		$txt .= '<ul>';

		foreach($file as $f) {
			$txt .= '<li>' . $f . '</li>';
		}
		$txt .= '</ul><br>';
		$txt .= '';
		return $txt;
	}

	public function updateFilePath($root, $files) {
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

	public function logging($projectId, $message, $customerCode, $so, $pi, $qa, $invoice, $filename, $source) {
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
				)",
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

			if (!$logging) {
				throw new \Exception('Save log failed.');
			}
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

  public function mapQuantation($quantation, $vouchercutstr)
{
  $conn = self::connect();
  return Sqlsrv::hasRows(
    $conn,
    "SELECT SQ.custaccount
  ,SQ.quotationid
  ,SQ.DATAAREAID
  ,SQ.CREATEDDATE
  -- ,SQ.TOPORT [CONDITION]
  ,SO.DSG_TOPORTID [CONDITION]
    FROM SalesQuotationTable SQ
    JOIN SALESTABLE SO
    ON SO.QUOTATIONID = SQ.QUOTATIONID
    AND SO.DATAAREAID = SQ.DATAAREAID
    JOIN CustPackingSlipJour CJ
    ON CJ.SALESID = SO.SALESID
    AND CJ.DATAAREAID = SO.DATAAREAID
    WHERE SQ.DATAAREAID = 'dsc'
    AND SQ.CUSTACCOUNT = 'C-1089'
    AND SQ.quotationid = ?
    AND CJ.VOUCHER_NO = ?
    AND SO.DSG_TOPORTID in('PTO','SVA','SJU','ABE','CRR','DUL','GRN','HOU','LBE','MES','BBA','CGO','ORK','DUB','GUA','HER','KLJ','KOP','LIV','PUE','RTM','SPD','SOU','VAR','BAJ','BUD','LIT','MRS','MIA','COG','PJD','WMT','BAQ','BUN','LAD','ZLO','MAM','MON','REY','ARI','ASU','CAU','GUY','RHN','SIO','PER','SAC','STX','NUL','SNL','FRS','STN','RIG','LAX','PEB','GAI')
    ORDER BY  SQ.CREATEDDATE DESC",
    [$quantation, $vouchercutstr]
  );
}
  public function getCustomerQuantation($file)
  {
    preg_match('/QA(?:..-......)/i', $file, $data);

    if (count($data) === 0) {
      return '';
    }

    return $data[0];
    // C0414-QA17-000207.docx
    // or C0414-QA17-000207#2.docx, C0414-QA17-000207#3.docx
    // $q = explode('.', $file)[0]; // C0414-QA17-000207
    // $_qa = explode('-', $q); // ['C0414', 'QA17', '000207']
    // if (count($_qa) < 3) { return 'FORMAT_INCORRECT';}
    // $_qa_2 = $_qa[1] . '-' . $_qa[2]; // QA17-000207 or QA17-000207#2
    // $_qa_final = explode('#', $_qa_2)[0]; // remove #
    // return $_qa_final; // return QA17-000207
    }
    public function mapQuantationdata($quantation,$voucher)
{
  $conn = self::connect();
  $sql = "SELECT SQ.custaccount
  ,SQ.quotationid
  ,SQ.DATAAREAID
  ,SQ.CREATEDDATE
  -- ,SQ.TOPORT [CONDITION]
  ,SO.DSG_TOPORTID [CONDITION]
    FROM SalesQuotationTable SQ
    JOIN SALESTABLE SO
    ON SO.QUOTATIONID = SQ.QUOTATIONID
    AND SO.DATAAREAID = SQ.DATAAREAID
    JOIN CustPackingSlipJour CJ
    ON CJ.SALESID = SO.SALESID
    AND CJ.DATAAREAID = SO.DATAAREAID
    WHERE SQ.DATAAREAID = 'dsc'
    AND SQ.CUSTACCOUNT = 'C-1089'
    AND SQ.quotationid = ?
    AND CJ.VOUCHER_NO = ?
    ORDER BY  SQ.CREATEDDATE DESC";
    $query = Sqlsrv::rows(
    $conn,
    $sql,
    [
      $quantation
      ,$voucher
    ]
  );

  return $query;

}



}
