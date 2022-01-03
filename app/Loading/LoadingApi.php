<?php 

namespace App\Loading;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class LoadingAPI {

	public function __construct() {
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
		$this->automail = new Automail;
	}

	public function getEmail($projectId,$companny,$checkCustoms)
	{
		if ($checkCustoms === "SUNISA") 
		{
			if ($companny === "DSL") 
			{
				$emailcate = 22;
			}
			else if ($companny === "DRB") 
			{
				$emailcate = 23;
			}
			else if ($companny === "DSI") 
			{
				$emailcate = 24;
			}
			else if ($companny === "SVO") 
			{
				$emailcate = 25;
			}
			else if ($companny === "STR") 
			{
				$emailcate = 26;
			}
			
		} 
		else if ($checkCustoms === "NICHAPAR") 
		{
			if ($companny === "DSL") 
			{
				$emailcate = 27;
			}
			else if ($companny === "DRB") 
			{
				$emailcate = 28;
			}
			else if ($companny === "DSI") 
			{
				$emailcate = 29;
			}
			else if ($companny === "SVO") 
			{
				$emailcate = 30;
			}
			else if ($companny === "STR") 
			{
				$emailcate = 31;
			}
		} 
		else 
		{
			$emailcate = 18;
		}

		$sql = "SELECT 
		E.Email, 
		E.EmailType, 
		EmailCategory
		FROM EmailLists E 
		WHERE ProjectID = $projectId
		AND EmailCategory = $emailcate
		AND [Status] = 1";

		$rows = Database::rows(
		$this->db_live,
		$sql
		);

		$to = [];
		$cc = [];
		$sender = "";

		foreach ($rows as $row) 
		{
			# code...
			
			if ($row["EmailType"] === 1) 
			{
				$to[] = $row["Email"];
			} 
			else if ($row["EmailType"] === 2) 
			{
				$cc[] = $row["Email"];
			} 
			else if ($row["EmailType"] === 4) 
			{
				$sender = $row["Email"];
			}
		}

		return ["to" => $to, "cc" => $cc ,"sender" => $sender]; //, "sender" => $sender
	}

	public function getCustomFromFileECS($filename) 
	{
		// preg_match_all('/DSC_(\d{4})_(\d+)[.]/i', $filename, $data);
		preg_match_all('/DSC_(\d{4})_(\d+)/i', $filename, $data);
		if (count($data[0]) === 0) {
			return [[]];
		}

		return $data[0];
	}

	public function IsCustomFromFileECS($filename) 
	{

		if (preg_match('/DSC_(\d{4})_(\d+)/i', $filename)) {
			return true;
		}
		else {
			return false;
		}
	}

	public function IsFormatRevised($filename)
	{
		if (preg_match('/Revised/i', $filename)) {
			return true;
		} else {
			return false;
		}
	}

	public function explodeFile($filename)
	{

		$file_extend = explode('.',$filename["file"]);
		$file_1 = explode('_',$file_extend[0]);

		if($filename["type"]=='revise')
		{
			$data = [
				"company" => $file_1[1],
				"year" => $file_1[2],
				"no" => preg_replace("[ ]","",$file_1[3])
			];
		}
		else
		{
			$data = [
				"company" => $file_1[0],
				"year" => $file_1[1],
				"no" => preg_replace("[ ]","",$file_1[2])
			];
		}
		return $data;

	}

	public function IsVoucher($company,$year,$voucher)
	{
		try 
		{
			$res = Database::rows(
				$this->db_ax,
				"SELECT *
				FROM CUSTCONFIRMJOUR C
				JOIN
				(
					SELECT C.DATAAREAID ,C.SALESID,MAX(C.RECID)[RECID]
					FROM CUSTCONFIRMJOUR C
					WHERE C.DSG_VOUCHERNO !=0
					GROUP BY C.DATAAREAID ,C.SALESID
				)C_MAX
				ON C_MAX.DATAAREAID = C.DATAAREAID
				AND C_MAX.SALESID = C.SALESID
				AND C_MAX.RECID = C.RECID
				WHERE
				C.DSG_VOUCHERTYPE = 'ESDO'
				AND C.DATAAREAID = ?
				AND C.DSG_VOUCHERSERIES = ?
				AND C.DSG_VOUCHERNO = ?",
				[
					$company,
					$year,
					$voucher
				]
			);

			if (count($res) == 0 ) 
			{
				return false;
			}
			else
			{
				return true;
			}
			
		} 
		catch (\Exception $e) 
		{
			return $e->getMessage();
		}

		
	}

	public function getSalesOrder($company,$year,$voucher)
	{
		try 
		{
			$res = Database::rows(
				$this->db_ax,
				"SELECT
				STUFF((
				  Select ','+ RTRIM(CASE WHEN ST.DSG_LOADINGPLANT = 'DSR' THEN 'SVO' ELSE UPPER(ST.DSG_LOADINGPLANT) END) [DSG_LOADINGPLANT]
							From SALESTABLE ST
								JOIN
								(
									SELECT DISTINCT CT.CONFIRMID,CT.DATAAREAID,CT.ORIGSALESID,CT.SALESID
									FROM CUSTCONFIRMTRANS CT
								)CT ON CT.DATAAREAID = ST.DATAAREAID
								AND CT.ORIGSALESID = ST.SALESID
								AND CT.DATAAREAID = ST.DATAAREAID
							WHERE
							CT.CONFIRMID = C.CONFIRMID
							AND CT.DATAAREAID = C.DATAAREAID
				  FOR XML PATH(''), TYPE).value('.', 'NVARCHAR(MAX)'), 1, 1, '')[DSG_LOADINGPLANT]

				,C.DSG_VOUCHERTYPE + '/' + CONVERT(NVARCHAR(10),C.DSG_VOUCHERSERIES) + '/' + CONVERT(NVARCHAR(10),C.DSG_VOUCHERNO) [VOUCHER]
				,ST.DSG_EDDDATE
				,ST.SALESNAME
				,UPPER(ST.DATAAREAID) + '-' + ST.QUOTATIONID [QUOTATIONID]
				,UPPER(ST.DATAAREAID) + '-' + ST.SALESID [SALESID]
				,ST.DSG_CustomsBy
				,UPPER(ST.DSG_CheckerCustomer) [DSG_CheckerCustomer]
				,UPPER(ST.DATAAREAID)[DATAAREAID]
				,UPPER(CASE WHEN C.DATAAREAID = 'DSR' THEN 'SVO' ELSE C.DATAAREAID END) + '/' + CONVERT(NVARCHAR(10),C.DSG_VOUCHERSERIES) + '/' + CONVERT(NVARCHAR(10),C.DSG_VOUCHERNO) [INVOICE]
				FROM CUSTCONFIRMJOUR C
				JOIN
				(
					SELECT C.DATAAREAID ,C.SALESID,MAX(C.RECID)[RECID]
					FROM CUSTCONFIRMJOUR C
					WHERE C.DSG_VOUCHERNO !=0
					GROUP BY C.DATAAREAID ,C.SALESID
				)C_MAX
				ON C_MAX.DATAAREAID = C.DATAAREAID
				AND C_MAX.SALESID = C.SALESID
				AND C_MAX.RECID = C.RECID
				JOIN SALESTABLE ST
				ON ST.SALESID = C.SALESID
				AND ST.DATAAREAID = C.DATAAREAID
				WHERE
				C.DSG_VOUCHERTYPE = 'ESDO'
				AND C.DATAAREAID = ?
				AND C.DSG_VOUCHERSERIES = ?
				AND C.DSG_VOUCHERNO = ?",
				[
					$company,
					$year,
					$voucher
				]
			);

			if ($res) 
			{
				return $res;
			}
			else
			{
				return [];
			}
			
		} 
		catch (\Exception $e) 
		{
			return $e->getMessage();
		}
	}

	public function explodeVoucher($voucher)
	{
		$voucher_ex =  explode(',',$voucher);
		if(count($voucher_ex)>0)
		{
			foreach ($voucher_ex as $key) {

			}
		}

		return $voucher_ex;

	}

	public function getSubject($filename,$get_so)
	{

		$file_extend = explode('.',$filename["file"]);
		$file_1 = explode('_',$file_extend[0]);

		if(count($file_1)===2)
		{

			$subject = "ใบโหลด/" . $get_so["SALESNAME"] . ':' . $get_so["INVOICE"] ;;
		}
		else
		{
			$subject =  $file_1[0] ."ใบโหลด/" . $get_so["SALESNAME"] . ':' . $get_so["INVOICE"] ;;
		}

		return $subject;
	}

	public function getBody($data)
	{

		$text = "<h3><font color='red'>เรียนคลังสินค้า</font></h3><br/><br/>";
		$text .= "<font color='red'>รบกวนจัดเตรียมและบรรจุสินค้าตามเอกสารไฟล์แนบคะ</font><br/><br/><br/>";
		$text .= '<b>Loading Plant : </b>'.$data["DSG_LOADINGPLANT"]	. '<br/><br/>';
		$text .= '<b>Invoice No. : </b>'.$data["INVOICE"]	.'<br/><br/>';
		$text .= '<b>Loading date : </b>'. date('d/m/Y', strtotime($data["DSG_EDDDATE"])) .'<br/><br/>';
		$text .= '<b>customer name : </b>'.$data["SALESNAME"]	.'<br/><br/>';
		$text .= '<b>PI no. : </b>'.$data["QUOTATIONID"]	.'<br/><br/>';
		$text .= '<b>Sale order no. : </b>'.$data["SALESID"]	.'<br/><br/>';
		$text .= '<b>Custom by : </b>'.$data["DSG_CustomsBy"]	.'<br/><br/>';
		$text .= '<b>Checker by : </b>'.$data["DSG_CheckerCustomer"]	.'<br/><br/>';

		return $text;
	}


}