<?php

namespace App\Api;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class ApiAPI {

	private $db_ax = null;
	private $db_live = null;
	private $automail = null;

	public function __construct() {
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
		$this->automail = new Automail;
  }

	public function getsubjectBooking() {
		try {
			return 'Booking API ';
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function isBookingReviseinternal($filename){
		if (preg_match("/-REV/i", $filename)) {
			return true;
		} else {
			return false;
		}
	}

	public function getMailCustomer($projectId) {
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
				if ($q['EmailType']==1 && $q['EmailCategory']==17) {
					$listsTo[] = $q['Email'];
				}else if($q['EmailType']==2 && $q['EmailCategory']==17){
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

	public function getSOFromFileBooking($filename) {
		preg_match_all('/SO(?:..-......)/i', $filename, $data);

		if ( count($data[0]) === 0) {
			return [[]];
		}

		return $data[0];
	}

	public function isBookingTFileMatchAx($filename) {
		try {
			$_SO = "S.SALESID IN ($filename)";
			//	preg_match_all('/SO(?:..-......)/i', $filename, $data);
 			$SO = [];
			$PO = [];
			$PI = [];
			$CY = [];
			$RTN = [];
			$SalName = [];
			$Cusref = [];
			$Loadingdate = [];
			$HC = [];
			$Booking_detail = [];
			$Booknum = [];
			$AGENT = [];

			$query = Database::rows(
				$this->db_ax,
				"SELECT S.SALESID ,
				S.QUOTATIONID,
				S.NoYesId_AddPI,
				S.DSG_CY,
				S.DSG_RTN,
				CT.NAME,
				S.CUSTOMERREF,
				S.DSG_EDDDate,
				DS.DSG_SUBHC,
				DS.DSG_BOOKINGDETAIL,
				S.DSG_PRIMARYAGENTID,
				S.DSG_BOOKINGNUMBER
				FROM SALESTABLE S
				LEFT JOIN DSG_SALESTABLE DS ON DS.SALESID = S.SALESID AND DS.DATAAREAID=S.DATAAREAID
				LEFT JOIN CustTable CT ON CT.ACCOUNTNUM = S.CUSTACCOUNT AND CT.DATAAREAID = S.DATAAREAID
				WHERE $_SO
				AND S.SALESSTATUS <> 4 --cancel
				AND S.INVOICEACCOUNT IN ('C-2720')
				AND S.DATAAREAID='DSC'"
			);

			foreach($query as $q) {

					$SO[] = $q['SALESID'];
					$PO[] = $q['CUSTOMERREF'];
					$PI[] = $q['QUOTATIONID'];
					$CY[] = date('d/m/Y',strtotime($q['DSG_CY']));
					$RTN[] =date('d/m/Y',strtotime($q['DSG_RTN']));
					$SalName[] = $q['NAME'];
					$Loadingdate[] = date('d/m/Y',strtotime($q['DSG_EDDDate']));
					$HC[] = $q['DSG_SUBHC'];
					$Booking_detail[] = $q['DSG_BOOKINGDETAIL'];
					$Booknum[] = $q['DSG_BOOKINGNUMBER'];
					$AGENT[] = $q['DSG_PRIMARYAGENTID'];

				}
				return [
					"SO" => $SO,
					 "PO" => $PO,
					"PI" => $PI,
					"CY" => $CY,
					"RTN" => $RTN,
					"SalName" => $SalName,
					"Cusref" => $Cusref,
					"Loadingdate" => $Loadingdate,
					"HC" => $HC,
					"Booking_detail" => $Booking_detail,
					"Numbook" => $Booknum,
					"AGENT" => $AGENT
				];
			} catch (\Exception $e) {
					return $e->getMessage();
				}
	}

	public function getBookingBody_v3($txtSo, $txtPo, $txtPI, $txtLd, $txtCy, $txtRtn, $txtHc, $txtBk, $AGENT) {
			$text = '';
			$txtAgent1 = '';
			$txtAgent = '';
			$SALESNAME = 'AMERICAN PACIFIC INDUSTRIES, INC.';;

		//	preg_match_all('/SO(?:..-......)/i', $so, $output_array);
			//
			// if (count($output_array[0])>1) {
			// 	for ($i=0; $i < count($output_array[0]); $i++) {
			// 		$dataso .= $output_array[0][0].",";
			// 		$textSO  = substr($dataso, 0, -1);
			// 	}
			// }else{
			// 	$textSO= $output_array[0][0];
			// }
	    //  ของพี่เจด้ของพี่เจด้านบน
			foreach ($AGENT as $value) {
				if(count($AGENT) >1){
					$txtAgent1 .= $value.',' ;
					$txtAgent = substr($txtAgent1,0,-1);
				}

				else {
								$txtAgent .= $value;
							}
			}
			$text .= 'Dear EL, <br><br>';
			$text .= '<b>Customer name : </b>' . $SALESNAME. '<br>';
			$text .= '<b>P/I : </b>' . $txtPI . '<br>';
			$text .= '<b>SO : </b>' . $txtSo . '<br>';
			$text .= '<b>PO : </b>' .$txtPo. '<br>';
			$text .= '<b>Loading date : </b>'. $txtLd;
			$text .= '<b> CY : </b>'. $txtCy;
			$text .= '<b> RTN : </b>'. $txtRtn.' <br>';
			$text .= '<b>Agent : </b>'. $txtAgent.'<br><br>';
			$text .= '<b>Sub\'HC : </b>'.$txtHc.' <br>';
			$text .= '<b>Booking Detail : </b><br>';

			$text .= '<ul>';

			if($txtBk !="") {
				$text .= '<li>'.$txtBk.'</li>';
			}
			else{
				$text .= '-';
			}
			$text .= '</ul><br>';

			return $text;
 	}

	public function getBookingSubject_internalAPI($SO, $name,$PO, $type, $Numbook ) {
		$text = '';
		//$name = 'AMERICAN PACIFIC INDUSTRIES, INC.';
		$numm = '';
		$numm1 = '';
		$NumbookDuplicate = array_unique( $Numbook ); // $NumbookDuplicate data array
		$arr_Numbook = array_filter( $NumbookDuplicate ); //cut array is null
		$nameTopic = '';
		foreach ($arr_Numbook as $value) {
			if(count($arr_Numbook) >1){
				$numm1 .= $value.',' ;
				$numm = substr($numm1,0,-1);
			}else {
							$numm .= $value;
						}
		}
		foreach ($name as $value) {
			$nameTopic .= $value;
		}
		if($type == 'New'){
			$text .= 'New Booking : ' . $nameTopic . ' / ' .
			$PO . ' / ' .$SO. ' / ' .$numm;
		}else{
			$text .= 'Revised Booking : ' . $nameTopic . ' / ' .
			$PO . ' / ' .$SO. ' / ' .$numm;
		}
		return $text;
	}

	public function getEmailList($projectId) {
		try {

			$listsToEx = [];
			$listsCCEx = [];
			$listsToIn = [];
			$listsToFailed = [];
			$sender = "";

			$query = Database::rows(
				$this->db_live,
				"SELECT * FROM EmailLists WHERE ProjectID=? AND Status=?",[$projectId,1]
			);

			foreach($query as $q) 
			{
				if ($q['EmailType']== 1 && $q['EmailCategory'] == 16) 
				{
					$listsToEx[] = $q['Email'];
				}
				else if ($q['EmailType']== 2 && $q['EmailCategory'] == 16) 
				{
					$listsCCEx[] = $q['Email'];
				}
				else if ($q['EmailType']== 1 && $q['EmailCategory'] == 17) 
				{
					$listsToIn[] = $q['Email'];
				}
				else if ($q['EmailType']== 5 && $q['EmailCategory'] == 17) 
				{
					$listsToFailed[] = $q['Email'];
				}
				else if ($q['EmailType']== 4 && $q['EmailCategory'] == 17) 
				{
					$sender = $q['Email'];
				}
			}

			return [
				'toEX' => $listsToEx,
				'ccEX' => $listsCCEx,
				'toIN' => $listsToIn,
				'toFailed' => $listsToFailed,
				'sender' => $sender
			];

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function isAPIFileMatchAx($pi,$inv) {
		try {

			$isExistsQA = Database::rows(
				$this->db_ax,
				"SELECT Q.QUOTATIONID,Q.SALESIDREF,C.SERIES,C.VOUCHER_NO
				FROM SALESQUOTATIONTABLE Q JOIN
				CUSTPACKINGSLIPJOUR C ON Q.SALESIDREF = C.SALESID
				WHERE Q.QUOTATIONID = ? AND 
				Q.DATAAREAID = 'DSC' AND C.DATAAREAID = 'DSC'
				AND Q.CUSTACCOUNT = 'C-2720' ",
				[ $pi ]
			);
            
			if (count($isExistsQA) <= 0) {
				return false;
			}

			if($isExistsQA[0]['VOUCHER_NO'] != $inv ){
				return false;
			}

			return true;
			
		} catch (\Exception $e) {
			return false;
		}	

    }
    
    public function Size($path) {
        $bytes = sprintf('%u', filesize($path));
        
        if ($bytes > 0){
            $unit = intval(log($bytes, 1024));
            $units = array('B', 'KB', 'MB', 'GB');

            if (array_key_exists($unit, $units) === true){
                return sprintf('%.2f %s', $bytes / pow(1024, $unit), $units[$unit]);
            }
        }
        
        return $bytes;
	}

	public function getSubject_CDS($file = [], $type) {
		try 
		{	
			if ($type=="CDS") 
			{
				preg_match('/QA(a?.........)/i', $file, $qa);
				preg_match('/INV(.+\d)/', $file, $inv);
				preg_match('/PO#(.+\d)\b/', $file, $po1);
				preg_match('/PO#(.+)-/', $po1[0], $output_po);

				$po = substr($output_po[0], 0, -1);

				$text = 'TEST !!!  TBC / Century Booking / ' . $qa[0] . '-' . $po . '-' . $inv[0] . '-VGM, INV,PL&INSP';
			}
			else if($type=="ERROR")
			{
				$text = 'TEST !!!  ERROR TBC / Century Booking';
			}

			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
    }

	public function getBody_CDS($file = []) {
		try 
		{
			preg_match('/QA(a?.........)/i', $file, $qa);
			preg_match('/INV(.+\d)/', $file, $inv);
			preg_match('/PO#(.+\d)\b/', $file, $po1);
			preg_match('/PO#(.+)-/', $po1[0], $output_po);

			$po = substr($output_po[0], 0, -1);


			$text = '';
			$text .= '<b>Dear Sir / Madam,</b>'.'<br><br>';
			$text .= '<b>Please see VGM, INV., PL.& INSP for ' . $qa[0] . '-' . $po . '-' . $inv[0] . '  as attached file.</b>' . '<br><br>';
			$text .= '<table>';
			$text .= '<tr>';
			$text .= '<td><b>Customer Name</b></td>';
			$text .= '<td><b>:</b> American Omni Trading Company</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>PI ID</b></td>';
			$text .= '<td><b>:</b> ' . $qa[0] . '</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>PO</b></td>';
			$text .= '<td><b>:</b> ' . $output_po[1] . '</td>';
			$text .= '</tr>';
			$getDataCDS = self::getDataCDS($qa[0]);
			$text .= '<tr>';
			$text .= '<td><b>Invoice No</b></td>';
			$text .= '<td><b>:</b> DSC/' . $getDataCDS[0]['SERIES'] . '/' . $getDataCDS[0]['VOUCHER_NO'] . '</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>SO ID</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['SALESIDREF'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>ETD</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_ETD'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>ETA</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_ETA'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Destination port</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_DESTINATION'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Agent</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_PRIMARYAGENTID'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Shipping Line</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_SHIPPINGLINEDESCRIPTION'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Container No.</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_CONTAINERNO'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td><b>Seal No.</b></td>';
			$text .= '<td><b>:</b> '.$getDataCDS[0]['DSG_SEALNO'].'</td>';
			$text .= '</tr>';
			$text .= '</table>';
			$text .= '<br><br><br>'.'<b>Best Regards,</b>';
			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getDataCDS($pi) {
		try {
            
			return Database::rows(
				$this->db_ax,

				"SELECT TOP 1 Q.QUOTATIONID
				,Q.SALESIDREF
				,C.SERIES
				,C.VOUCHER_NO
				,CONVERT(VARCHAR,C.DSG_ETD,106) AS DSG_ETD
				,CONVERT(VARCHAR,C.DSG_ETA,106) AS DSG_ETA
				,C.DSG_DESTINATIONCODEDESCRI50019 AS DSG_DESTINATION
				,S.DSG_PRIMARYAGENTID
				,S.DSG_SHIPPINGLINEDESCRIPTION
				,C.DSG_CONTAINERNO
				,C.DSG_SEALNO
				FROM SALESQUOTATIONTABLE Q JOIN
				CUSTPACKINGSLIPJOUR C ON Q.SALESIDREF = C.SALESID  JOIN
				SALESTABLE S ON Q.SALESIDREF = S.SALESID
				WHERE Q.QUOTATIONID = ? AND
				Q.DATAAREAID = 'DSC' AND C.DATAAREAID = 'DSC'
				AND S.DATAAREAID = 'DSC'",
				[
					$pi
				]
			);
		} catch (\Exception $th) {
			return $e->getMessage();
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

	public function getBodyCDS_Failed($file = [], $remark) {
		try {

			// $remark = 'Fail sending due to no date of sending.';
			$text = '';
			$text .= '<b>Dear Team,</b>'.'<br><br>';
			
			$text .= '<b>รายชื่อไฟล์ที่ไม่สามารถส่งให้ลูกค้าได้ รายละเอียดตามไฟล์แนบ</b><br><br>';

			$text .= '<b>สาเหตุ : ' . $remark . '</b><br>';

			$text .= '<ul>';

			foreach($file as $f) {
				$text .= '<li>' . $f . '</li>';
			}
			$text .= '</ul><br>';

			$text .= '';

			return $text;

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

}
