<?php 

namespace App\Aot;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class AotAPI {

	public function __construct() {
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
		$this->automail = new Automail;
    }
	
	public function getTbcSubjectDaily() {
		try {
			return 'AOT VGM INV PL daily Report TBC (Daily report)';
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getTbcBodyDaily() {
		try {
			$text = '';
			$text .= 'Dear All,'.'<br><br>';
			$text .= 'Please see AOT_ VGM+INV+PL daily Report_TBC as attached for you reference.';
			return $text;
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
				}
			}

			return [
				'to' => $listsTo,
				'cc' => $listsCC,
				'internal' => $listsInternal,
				'internalcc' => $listsInternalCC,
				'sender' => $listsSender
			];

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

    public function getTbcSubject($file = [] , $type) {
		try {
			
			$f = [];
			$txt = '';
			$cnt = '';
			for ($i=0; $i < count($file); $i++) { 
				preg_match('/CNT(a?.........)/i', $file[$i], $output_array);
				if($f!=$cnt){
					array_push($f,$output_array[0]);
				}
				$cnt = $f;
			}
			$txt .= implode(" & ",$f);
			
			if ($type=="SI") {
				$text = 'TBC / Century Booking / ' . $txt . '_Shipping instruction';
			}else if($type=="VGM"){
				$text = 'TBC / Century Booking / ' . $txt . '_VGM, INV & PL, INSP';
			}else if($type=="ERROR"){
				$text = 'ERROR TBC / Century Booking / ' . $txt;
			}
			
			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
    }
    
    public function getTbcSISubject($file = [] , $type) {
		try {
			
			$f = [];
			$txt = '';
			$cnt = '';
			for ($i=0; $i < count($file); $i++) { 
				preg_match('/CNT(a?.........)/i', $file[$i], $output_array);
				array_push($f,$output_array[0]);
			}
			$txt .= implode(" & ",$f);
			
			if ($type=="SI") {
				$text = 'TBC / Century Booking / ' . $txt . '_Shipping instruction';
			}else if($type=="ERROR"){
				$text = 'ERROR TBC / Century Booking / ' . $txt . '_Shipping instruction';
			}
			
			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
    }

    public function isAOTFileMatchSo($filename) {
		try {
            
            preg_match_all('/(CNT\\d+(\/|_|#|-|)\\d+|CNT\\d+)/i', $filename, $data);

			if (count($data[0]) <= 0) {
				return false;
			}

			$isExists = Database::rows(
				$this->db_ax,
				"SELECT S.SALESID FROM SALESTABLE S
				WHERE S.CustomerRef = ?
				AND S.SALESSTATUS <> 4 --cancel
				AND S.INVOICEACCOUNT IN ('C-0447', 'C-2637', 'C-2693')",
				[ $data[0][0] ]
			);
            
			if (count($isExists) <= 0) {
				return false;
			}else{
				return true;
			}

		} catch (\Exception $e) {
			return false;
		}	

    }

    public function isAOTFileMatchAx($filename) {
		try {
            
            preg_match_all('/(CNT\\d+(\/| |#|-|)\\d+|CNT\\d+)/i', $filename, $data);

			if (count($data[0]) <= 0) {
				return false;
			}

			$isExists = Database::rows(
				$this->db_ax,
				"SELECT S.SALESID FROM SALESTABLE S
				WHERE S.CustomerRef = ?
				AND S.SALESSTATUS <> 4 --cancel
				AND S.INVOICEACCOUNT IN ('C-0447', 'C-2637', 'C-2693')",
				[ $data[0][0] ]
			);
            
			if (count($isExists) <= 0) {
				return false;
			}

			$isSendCopyOfDocsNull = Database::rows(
				$this->db_ax,
				"SELECT TOP 1
				C.SALESID,
				C.INVOICEACCOUNT,
				C.DSG_Send_copy_date
				FROM CustPackingSlipJour C
				WHERE C.SALESID = ?
				ORDER BY C.PACKINGSLIPID DESC",
				[ $isExists[0]['SALESID'] ]
			);

			// $sendCopyOfDocsDate = date('Y-m-d', strtotime($isSendCopyOfDocsNull[0]['DSG_Send_copy_date']));
			$sendCopyOfDocsDate='';
			if(isset($isSendCopyOfDocsNull[0]['DSG_Send_copy_date'])){
				$sendCopyOfDocsDate = date('Y-m-d', strtotime($isSendCopyOfDocsNull[0]['DSG_Send_copy_date']));
			}
            
			if ($sendCopyOfDocsDate === '1900-01-01' || 
				$sendCopyOfDocsDate === '1970-01-01' 
			) {
                return false;
			} else {
                return true;
			}

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
	
	public function getBodyTbcSI($file = []) {
		try {
			$text = '';
			$text .= 'Dear Sir / Madam,'.'<br><br>';
			$text .= 'Please see shipping instuction as the attached. Please submit to Century. Thank you very much.'. '<br><br>';

			for ($i=0; $i < count($file); $i++) { 
				preg_match('/CNT(a?.........)/i', $file[$i], $output_array);
				
				$text .= '<table>';
				$text .= '<tr>';
				$text .= '<td>Customer Name</td>';
				$text .= '<td>: American Omni Trading Company</td>';
				$text .= '</tr>';
				$text .= '<tr>';
				$text .= '<td>Count Code</td>';
				$text .= '<td>: '.$output_array[0].'</td>';
				$text .= '</tr>';
				$getDataSI = self::getDataSI($output_array[0]);
				$text .= '<tr>';
				$text .= '<td>Product</td>';
				$text .= '<td>: '.$getDataSI[0]['SUPITEM'].'</td>';
				$text .= '</tr>';
				$text .= '<tr>';
				$text .= '<td>Destination Port</td>';
				$text .= '<td>: '.$getDataSI[0]['DSG_TOPORTDESC'].'</td>';
				$text .= '</tr>';
				$text .= '<tr>';
				$text .= '<td>Quotation ID</td>';
				$text .= '<td>: '.$getDataSI[0]['QUOTATIONID'].'</td>';
				$text .= '</tr>';
				$text .= '<tr>';
				$text .= '<td>Sale ID</td>';
				$text .= '<td>: '.$getDataSI[0]['SALESID'].'</td>';
				$text .= '</tr>';
				$text .= '<tr>';
				$text .= '<td>Loading Date</td>';
				$text .= '<td>: '.$getDataSI[0]['DSG_EDDDATE'].'</td>';
				$text .= '</tr>';
				$text .= '<tr>';
				$text .= '<td>ETD</td>';
				$text .= '<td>: '.$getDataSI[0]['DSG_ETDDATE'].'</td>';
				$text .= '</tr>';
				$text .= '<tr>';
				$text .= '<td>ETA</td>';
				$text .= '<td>: '.$getDataSI[0]['DSG_ETADATE'].'</td>';
				$text .= '</tr>';
				$text .= '<tr>';
				$text .= '<td>Forwarder</td>';
				$text .= '<td>: CDS TH</td>';
				$text .= '</tr>';
				$getContainer = self::getContainer($getDataSI[0]['SALESID']);
				$text .= '<tr>';
				$text .= '<td>Container</td>';
				$text .= '<td>: '.implode(" / ",$getContainer).'</td>';
				$text .= '</tr>';
				$getGrossWeight = self::getGrossWeight($getDataSI[0]['SALESID']);
				$text .= '<tr>';
				$text .= '<td>G.W. Kgs</td>';
				$text .= '<td>: '.number_format($getGrossWeight,2).'</td>';
				$text .= '</tr>';
				$getContainerM3 = self::getContainerM3($getDataSI[0]['SALESID']);
				$text .= '<tr>';
				$text .= '<td>M3</td>';
				$text .= '<td>: '.implode(" / ",$getContainerM3).'</td>';
				$text .= '</tr>';
				$text .= '</table>';
				$text .= '<br><br>';
			}

			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getBodyTbcSIFailed($file = [], $remark) {
		try {

			// $remark = 'Fail sending due to no date of sending.';
			$text = '';
			$text .= 'Dear Team Shipping,'.'<br><br>';
			
			$text .= 'รายชื่อไฟล์ที่ไม่สามารถส่งให้ลูกค้าได้ รายละเอียดตามไฟล์แนบ<br><br>';

			$text .= 'สาเหตุ : ' . $remark . '<br>';

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

	public function getMailFailed($projectId) {
		try {
			
			$listsFailed = [];
				
			$query = Database::rows(
				$this->db_live,
				"SELECT * FROM EmailLists WHERE ProjectID=? AND Status=?",[$projectId,1]
			);

			foreach($query as $q) {
				if ($q['EmailType']==5 && $q['EmailCategory']==17) {
					$listsFailed[] = $q['Email'];
				}
			}

			return [
				'failed' => $listsFailed
			];

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getBodyTbcVGM($file = []) {
		try {

			$f = [];
			$txt = '';
			$cnt = '';
			for ($i=0; $i < count($file); $i++) { 
				preg_match('/CNT(a?.........)/i', $file[$i], $output_array);
				if($f!=$cnt){
					array_push($f,$output_array[0]);
				}
				$cnt = $f;
			}
			$txt .= implode(" & ",$f);

			$text = '';
			$text .= 'Dear Sir / Madam,'.'<br><br>';
			$text .= 'Please see VGM, INV. & PL. for ' .$txt. ' as attached file.' . '<br><br>';
			$text .= '<table>';
			$text .= '<tr>';
			$text .= '<td>Customer Name</td>';
			$text .= '<td>: American Omni Trading Company</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td>Count Code</td>';
			$text .= '<td>: '.$txt.'</td>';
			$text .= '</tr>';
			$getDataVGM = self::getDataVGM($txt);
			$text .= '<tr>';
			$text .= '<td>Product</td>';
			$text .= '<td>: '.$getDataVGM[0]['SUPITEM'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td>Destination Port</td>';
			$text .= '<td>: '.$getDataVGM[0]['DSG_TOPORTDESC'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td>Quotation ID</td>';
			$text .= '<td>: '.$getDataVGM[0]['QUOTATIONID'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td>Sale ID</td>';
			$text .= '<td>: '.$getDataVGM[0]['SALESID'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td>Loading Date</td>';
			$text .= '<td>: '.$getDataVGM[0]['DSG_EDDDATE'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td>ETD</td>';
			$text .= '<td>: '.$getDataVGM[0]['DSG_ETDDATE'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td>ETA</td>';
			$text .= '<td>: '.$getDataVGM[0]['DSG_ETADATE'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td>Return date</td>';
			$text .= '<td>: '.$getDataVGM[0]['DSG_RTN'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td>Forwarder</td>';
			$text .= '<td>: CDS TH</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$getContainer = self::getContainer($getDataVGM[0]['SALESID']);
			$text .= '<tr>';
			$text .= '<td>Container</td>';
			$text .= '<td>: '.implode(" / ",$getContainer).'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td>Invoice No.</td>';
			$text .= '<td>: '.$getDataVGM[0]['INVOICENO'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td>Container No.</td>';
			$text .= '<td>: '.$getDataVGM[0]['DSG_CONTAINERNO'].'</td>';
			$text .= '</tr>';
			$text .= '<tr>';
			$text .= '<td>Seal No.</td>';
			$text .= '<td>: '.$getDataVGM[0]['DSG_SEALNO'].'</td>';
			$text .= '</tr>';
			$text .= '</table>';
			return $text;
		} catch (\Exception $e) {
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

	public function KeepLogFile($file) {
		try {
			return Database::rows(
				$this->db_live,
				"SELECT TOP 10 * FROM Logs 
				WHERE FileName = ?
				AND Message = ?",
				[
					$file,
					'Keep File'
				]
			);
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getDataVGM($cnt) {
		try {

			$isExists = Database::rows(
				$this->db_ax,
				"SELECT S.SALESID FROM SALESTABLE S
				WHERE S.CustomerRef = ?
				AND S.SALESSTATUS <> 4 --cancel
				AND S.INVOICEACCOUNT IN ('C-0447', 'C-2637', 'C-2693')",
				[ $cnt ]
			);
            
			return Database::rows(
				$this->db_ax,

				"SELECT TOP 1 SL.SALESID,S.DSG_TOPORTID,S.DSG_TOPORTDESC,S.QUOTATIONID,
				CASE WHEN SL.DSG_DATE IS NULL THEN ''
				WHEN SL.DSG_DATE ='1900-01-01 00:00:00.000' THEN ''
				ELSE DATENAME(day,SL.DSG_DATE)+' '+DATENAME(month,SL.DSG_DATE)+' '+CONVERT(VARCHAR,YEAR(SL.DSG_DATE)) END [DSG_EDDDATE],
				CASE WHEN SL.DSG_ETD IS NULL THEN ''
				WHEN SL.DSG_ETD ='1900-01-01 00:00:00.000' THEN ''
				ELSE DATENAME(day,SL.DSG_ETD)+' '+DATENAME(month,SL.DSG_ETD)+' '+CONVERT(VARCHAR,YEAR(SL.DSG_ETD)) END [DSG_ETDDATE],
				CASE WHEN SL.DSG_ETA IS NULL THEN ''
				WHEN SL.DSG_ETA ='1900-01-01 00:00:00.000' THEN ''
				ELSE DATENAME(day,SL.DSG_ETA)+' '+DATENAME(month,SL.DSG_ETA)+' '+CONVERT(VARCHAR,YEAR(SL.DSG_ETA)) END [DSG_ETADATE],
				S.DSG_PrimaryAgentId,
				CASE WHEN S.DSG_RTN IS NULL THEN ''
				WHEN S.DSG_RTN ='1900-01-01 00:00:00.000' THEN ''
				ELSE DATENAME(day,S.DSG_RTN)+' '+DATENAME(month,S.DSG_RTN)+' '+CONVERT(VARCHAR,YEAR(S.DSG_RTN)) END [DSG_RTN],
				SUPITEM = STUFF(
						(SELECT ',' + SLL.DSG_MKSUBPRODUCTGROUPID
						FROM (
								SELECT SLL.SALESID,TT.DSG_MKSUBPRODUCTGROUPID,SLL.DATAAREAID
								FROM SALESLINE SLL 
								JOIN INVENTTABLE TT
								ON TT.ITEMID = SLL.ITEMID
								AND TT.DATAAREAID = 'DV'
								AND SLL.NOYESNOTCAL = 0
								GROUP BY SLL.SALESID,TT.DSG_MKSUBPRODUCTGROUPID,SLL.DATAAREAID				  
							)SLL
						WHERE SLL.SALESID = SL.SALESID AND SLL.DATAAREAID = SL.DATAAREAID FOR XML PATH ('')), 1, 1, '' ),
						UPPER(SL.DATAAREAID) + '/' + SL.SERIES+ '/' + convert(varchar,SL.VOUCHER_NO)  as INVOICENO ,
				CASE SL.DSG_CONTAINERNO  
				WHEN '' THEN '-'
				ELSE SL.DSG_CONTAINERNO  END AS DSG_CONTAINERNO ,
				CASE SL.DSG_SEALNO 
				WHEN '' THEN '-'
				ELSE SL.DSG_SEALNO END AS DSG_SEALNO
				FROM CUSTPACKINGSLIPJOUR SL INNER JOIN
				SALESTABLE S ON SL.SALESID = S.SALESID AND SL.DATAAREAID = S.DATAAREAID
				WHERE SL.SALESID = ? AND SL.DATAAREAID = ?
				ORDER BY SL.PACKINGSLIPID DESC",
				[
					$isExists[0]['SALESID'],
					'dsc'
				]
			);
		} catch (\Exception $th) {
			return $e->getMessage();
		}
	}
	
	public function getDataSI($cnt) {
		try {

			$isExists = Database::rows(
				$this->db_ax,
				"SELECT S.SALESID FROM SALESTABLE S
				WHERE S.CustomerRef = ?
				AND S.SALESSTATUS <> 4 --cancel
				AND S.INVOICEACCOUNT IN ('C-0447', 'C-2637', 'C-2693')",
				[ $cnt ]
			);
            
			return Database::rows(
				$this->db_ax,

				"SELECT TOP 1 
						S.SALESID
						,S.DSG_TOPORTID
						,S.DSG_TOPORTDESC
						,S.QUOTATIONID 
						,CASE WHEN S.DSG_EDDDATE IS NULL THEN ''
							WHEN S.DSG_EDDDATE ='1900-01-01 00:00:00.000' THEN ''
						ELSE DATENAME(day,S.DSG_EDDDATE)+' '+DATENAME(month,S.DSG_EDDDATE)+' '+CONVERT(VARCHAR,YEAR(S.DSG_EDDDATE)) END [DSG_EDDDATE],
						CASE WHEN S.DSG_ETDDATE IS NULL THEN ''
							WHEN S.DSG_ETDDATE ='1900-01-01 00:00:00.000' THEN ''
						ELSE DATENAME(day,S.DSG_ETDDATE)+' '+DATENAME(month,S.DSG_ETDDATE)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETDDATE)) END [DSG_ETDDATE],
						CASE WHEN S.DSG_ETADATE IS NULL THEN ''
							WHEN S.DSG_ETADATE ='1900-01-01 00:00:00.000' THEN ''
						ELSE DATENAME(day,S.DSG_ETADATE)+' '+DATENAME(month,S.DSG_ETADATE)+' '+CONVERT(VARCHAR,YEAR(S.DSG_ETADATE)) END [DSG_ETADATE]
						,S.DSG_PrimaryAgentId
						,SUPITEM = STUFF(
							(SELECT ',' + SLL.DSG_MKSUBPRODUCTGROUPID
							FROM 
							(
								SELECT SLL.SALESID,TT.DSG_MKSUBPRODUCTGROUPID,SLL.DATAAREAID
								FROM SALESLINE SLL 
								JOIN INVENTTABLE TT
								ON TT.ITEMID = SLL.ITEMID
								AND TT.DATAAREAID = 'DV'
								AND SLL.NOYESNOTCAL = 0
								--WHERE SLL.SALESID = 'SO19-017279'
								GROUP BY SLL.SALESID,TT.DSG_MKSUBPRODUCTGROUPID,SLL.DATAAREAID				  
							)SLL
						
							WHERE SLL.SALESID = S.SALESID 
							AND SLL.DATAAREAID = S.DATAAREAID
							FOR XML PATH ('')), 1, 1, ''
						)
							
				FROM SALESTABLE S  
				WHERE S.SALESID = ? AND S.DATAAREAID = ?",
				[
					$isExists[0]['SALESID'],
					'dsc'
				]
			);
		} catch (\Exception $th) {
			return $e->getMessage();
		}
	}

	public function getContainer($so) {
		try {
			$sql = Database::rows(
				$this->db_ax,
				"SELECT 
						SUM(S.DSG_CONTAINER1X20) [STD20]
						,SUM(S.DSG_CONTAINER1X40) [STD40]
						,SUM(S.DSG_CONTAINER1X40HC) [HC40]
				FROM SALESTABLE S
				JOIN 
					(
					SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,ORIGSALESID
					FROM CUSTCONFIRMSALESLINK SL
					GROUP BY DATAAREAID,ORIGSALESID
					)SL
					ON SL.DATAAREAID = S.DATAAREAID
					AND SL.ORIGSALESID = S.SALESID
					JOIN CUSTCONFIRMSALESLINK CSL
					ON CSL.DATAAREAID = S.DATAAREAID
					AND CSL.ORIGSALESID = S.SALESID
					AND CSL.CONFIRMID = SL.CONFIRMID
				WHERE CSL.SALESID =? AND S.DATAAREAID = ?",[$so,'dsc']
			);
			$container = [];
			foreach ($sql as $value) {
				if($value['STD20']>0){
					array_push($container,floor($value['STD20']) . "x20'");
				}
				if($value['STD40']>0){
					array_push($container,floor($value['STD40']) . "x40'");
				}
				if($value['HC40']>0){
					array_push($container,floor($value['HC40']) . "x40HC'");
				}
			}
			return $container;
		} catch (\Exception $th) {
			return $e->getMessage();
		}
	}

	public function getContainerM3($so) {
		try {
			$sql = Database::rows(
				$this->db_ax,
				"SELECT 
						SUM(S.DSG_CONTAINER1X20) [STD20]
						,SUM(S.DSG_CONTAINER1X40) [STD40]
						,SUM(S.DSG_CONTAINER1X40HC) [HC40]
				FROM SALESTABLE S
				JOIN 
					(
					SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,ORIGSALESID
					FROM CUSTCONFIRMSALESLINK SL
					GROUP BY DATAAREAID,ORIGSALESID
					)SL
					ON SL.DATAAREAID = S.DATAAREAID
					AND SL.ORIGSALESID = S.SALESID
					JOIN CUSTCONFIRMSALESLINK CSL
					ON CSL.DATAAREAID = S.DATAAREAID
					AND CSL.ORIGSALESID = S.SALESID
					AND CSL.CONFIRMID = SL.CONFIRMID
				WHERE CSL.SALESID =? AND S.DATAAREAID = ?",[$so,'dsc']
			);
			$container = [];
			foreach ($sql as $value) {
				if($value['STD20']>0){
					array_push($container,number_format($value['STD20']*30,2));
				}
				if($value['STD40']>0){
					array_push($container,number_format($value['STD40']*60,2));
				}
				if($value['HC40']>0){
					array_push($container,number_format($value['HC40']*70,2));
				}
			}
			return $container;
		} catch (\Exception $th) {
			return $e->getMessage();
		}
	}

	public function getGrossWeight($so) {
		try {
			$sql = Database::rows(
				$this->db_ax,
				"SELECT 
					-- SUM(ROUND((ISNULL(CT.QTY/PU.FACTOR,0)*IPM.PACKINGUNITWEIGHT)+(SOL.DSG_ITEMNETWEIGHT*CT.QTY/1000),2)) AS TOTALGROSSWEIGHT
					SUM(ROUND((ISNULL(CT.QTY/PU.FACTOR,0)*IPM.PACKINGUNITWEIGHT)+(SOL.DSG_ITEMNETWEIGHT*CT.DSG_Custom_QTY/1000),2)) AS TOTALGROSSWEIGHT
				FROM SALESTABLE SO JOIN 
					(
					SELECT MAX(CONFIRMID)CONFIRMID,DATAAREAID,ORIGSALESID
					FROM CUSTCONFIRMSALESLINK SL
					GROUP BY DATAAREAID,ORIGSALESID
					)SL
					ON SL.DATAAREAID = SO.DATAAREAID
					AND SL.ORIGSALESID = SO.SALESID
					JOIN CUSTCONFIRMSALESLINK CSL
					ON CSL.DATAAREAID = SO.DATAAREAID
					AND CSL.ORIGSALESID = SO.SALESID
					AND CSL.CONFIRMID = SL.CONFIRMID
					JOIN CUSTCONFIRMTRANS CT
					ON CT.DATAAREAID = CSL.DATAAREAID
					AND CT.CONFIRMID = CSL.CONFIRMID
					AND CT.ORIGSALESID = CSL.ORIGSALESID
					JOIN INVENTTABLE IT
					ON IT.ITEMID = CT.ITEMID
					AND IT.DATAAREAID = 'DV'
					JOIN INVENTPACKAGINGGROUP PG
					ON PG.DATAAREAID = 'DV'
					AND PG.PACKAGINGGROUPID = IT.PACKAGINGGROUPID
					JOIN INVENTPACKAGINGUNIT PU
					ON PU.DATAAREAID = 'DV'
					AND PU.ITEMRELATION = PG.PACKAGINGGROUPID
					JOIN INVENTPACKAGINGUNITMATERIAL IPM
					ON IPM.DATAAREAID = 'DV'
					AND IPM.PACKINGUNITRECID = PU.RECID
					JOIN SALESLINE SOL
					ON SOL.DATAAREAID = CT.DATAAREAID
					AND SOL.SALESID = CT.ORIGSALESID
					AND SOL.ITEMID = CT.ITEMID
				WHERE CSL.SALESID = ? AND SO.DATAAREAID = ?",[$so,'dsc']
			);

			return $sql[0]['TOTALGROSSWEIGHT'];
		} catch (\Exception $th) {
			return $e->getMessage();
		}
	}

	public function loggingKeep($projectId, $message, $customerCode, $so, $pi, $qa, $invoice, $filename, $source) {
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

			return $logging;

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getSubjectShippingdocinsp($file = [], $type) {
		try {
			$f = [];
			$txt = '';
			$cnt = '';
			for ($i=0; $i < count($file); $i++) { 
				preg_match('/CNT(a?.........)/i', $file[$i], $output_array);
				if($output_array[0]!=$cnt){
					array_push($f,$output_array[0]);
				}
				$cnt = $output_array[0];
			}
			$txt .= implode(" , ",$f);
			
			if($type=="DOCS_INSP"){
				$text = 'NEW AOTC DOCS : ' . $txt;
			}else if($type=="ERROR"){
				$text = 'ERROR NEW AOTC DOCS : ' . $txt;
			}
			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getBodyShippingdocinsp() {
		try {
			$text = '';
			$text .= 'Dear Sir / Madam,<br/><br/>';
			$text .= 'Please see shipping document as the attached. <br/><br/>';
			$text .= 'This e-mail is automatically generated. <br/> The information contained in this message is privileged and intended only for the recipients named. If the reader is not a representative of the intended recipient, any review, dissemination or copying of this message or the information it contains is prohibited. If you have received this message in error, please immediately notify the sender, and delete the original message and attachments.';
		return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getSubjectShippingdocinspAcc($file = []) {
		try {

			$files = [];
			$txt = '';
			$cnt = '';
			for ($i=0; $i < count($file); $i++) { 
				preg_match('/CNT(a?.........)/i', $file[$i], $output_array);
				if($output_array[0]!=$cnt){
					array_push($files,$output_array[0]);
				}
				$cnt = $output_array[0];
			}
			
			$refPi = [];
			$txtSubject =  '';

			foreach ($files as $f) {
				$refPi[] = trim(explode(' ', $f)[0]);
			}

			foreach ($refPi as $userPi) {
				$inv = self::getINVFromPI($userPi);
				$txtSubject .= $inv . ', ';
			}

			$txtSubject = trim($txtSubject, ', ');
			return 'NEW AOTC DOCS : ' . $txtSubject;

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getINVFromPI($user_po) {
		try {
		
			if (substr($user_po, 0, 1) === '0') {
				$po = (int)$user_po;
			} else {
				$po = $user_po;
			}

			$data = Database::rows(
				$this->db_ax,
				"SELECT 
					C.Ref_PI_No, 
					UPPER(C.DATAAREAID)+'/'+C.SERIES+'/'+CONVERT(NVARCHAR(10),C.VOUCHER_NO) [INV]
					FROM CustPackingSlipJour C 
					WHERE Ref_PI_No is not null 
					AND Ref_PI_No <> ''
					AND REF_PI_NO LIKE '%$po%'"
			);

			if ( count($data) === 0) {
				return '-';
			} else {
				return $data[0]['INV'];
			}

		} catch (Exception $e) {
			
		}
	}

	public function pathTofileDocs($files = [], $root) {
		try {
			$file = [];
            for ($x=0; $x < count($files); $x++) { 
				if(substr($files[$x], 13, 4)==="DOCS"){
					$file[] = $root.$files[$x];
				}
            }
            return $file;
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}

}