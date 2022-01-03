<?php 

namespace App\TireGroup;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class TireGroupAPI {

	public function __construct() {
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
		$this->automail = new Automail;
	}

	public function getSOAndCustomerMatched($so)
	{
		$rows = Database::rows(
			$this->db_ax,
			"SELECT ST.SALESID
				,ST.CUSTACCOUNT
				,ST.QUOTATIONID
				,ST.DSG_TOPORTID [CONDITION] 
			FROM SALESTABLE ST
			WHERE ST.SALESID = ?
			AND ST.CUSTACCOUNT = 'C-1089'
			AND ST.CUSTGROUP = 'ovs'
			AND ST.DATAAREAID = 'dsc'",
			[
				$so
			]
		);

		return $rows;
	}	

	public function getMailCustomer($projectId, $port) 
	{
		try {
			
			$listsTo = [];
			$listsCC = [];
			$listsInternal = [];
			$listsInternal2 = [];
			$listsgroupshipping = [];

			$query = Database::rows(
				$this->db_live,
				"SELECT * FROM EmailLists WHERE ProjectID=? AND Port=?",[$projectId,$port]
			);

			$query_internal2 = Database::rows(
				$this->db_live,
				"SELECT * FROM EmailLists WHERE ProjectID=?",[32]
			);

			foreach($query as $q) {
				if ($q['EmailType']==1 && $q['EmailCategory']==16) {
					$listsTo[] = $q['Email'];
				}else if($q['EmailType']==2 && $q['EmailCategory']==16){
					$listsCC[] = $q['Email'];
				}else if($q['EmailType']==1 && $q['EmailCategory']==17){
					$listsInternal[] = $q['Email'];
				// }else if($q['EmailType']==1 && $q['EmailCategory']==18){
				// 	$listsInternal2[] = $q['Email'];
				}
			}

			foreach ($query_internal2 as $q2) {
				if($q2['EmailType']==1 || $q2['EmailType']==2 && $q2['EmailCategory']==18){
					$listsInternal2[] = $q2['Email'];
				}
			}

			$listsSender = Database::rows(
				$this->db_live,
				"SELECT TOP 1 * FROM EmailLists WHERE ProjectID=? AND EmailType=?",[$projectId,4]
			);

			$listsgroupshipping = Database::rows(
				$this->db_live,
				"SELECT TOP 1 Email FROM EmailLists WHERE ProjectID=? AND EmailType=? AND EmailCategory=?",[$projectId,1,19]
			);

			return [
				'to' => $listsTo,
				'cc' => $listsCC,
				'internal' => $listsInternal,
				'internal2' => $listsInternal2,
				'groupshipping' => $listsgroupshipping[0]['Email'],
				'sender' => $listsSender[0]['Email']
			];

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getBookingSubject($file, $type = 'new') 
	{
		try {
			$text = '';
			$so = self::getSOFromFileBooking($file);
			$customer = self::getCustomerCode($file);
			if ($type === 'revised') {
				$text .= 'Revised Booking : ' . self::getCustNameFromSO($so[0], $customer) . ' / ' . self::getQABySO($so[0], $customer);
			} else {
				$text .= 'New Booking : ' . self::getCustNameFromSO($so[0], $customer) . ' / ' . self::getQABySO($so[0], $customer);			
			}

			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getCustNameFromSO($so, $customer) 
	{
		try {
			$res = Database::rows(
				$this->db_ax,
				"SELECT ct.NAME FROM SALESTABLE st
				LEFT JOIN CUSTTABLE ct ON ct.accountnum = st.custaccount
				WHERE st.salesid = ?
				AND st.CUSTACCOUNT = ?
				AND st.DATAAREAID = 'dsc'",
				[
					$so,
					$customer
				]
			);

			if ( count($res) !== 0) {
				return $res[0]['NAME'];
			} else {
				return '-';
			}
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getQABySO($so, $customer) 
	{
		try {
			$res = Database::rows(
				$this->db_ax,
				"SELECT QUOTATIONID
				FROM SALESTABLE
				WHERE SALESID = ?
				AND CUSTACCOUNT = ?
				AND QUOTATIONID is not null
				AND QUOTATIONID <> ''
				AND DATAAREAID = 'dsc'",
				[
					$so,
					$customer
				]
			);

			if (count($res) !== 0) {
				return $res[0]['QUOTATIONID'];
			} else {
				return '';
			}
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getSOFromFileBooking($filename) 
	{
		try {
			preg_match_all('/SO(?:..-......)/i', $filename, $data);
			if ( count($data[0]) === 0) {
				return [[]];
			}
			return $data[0];
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getCustomerCode($file) 
	{
		try {
			preg_match('/C.*?(\\d+)/i', $file, $data);
			return substr_replace($data[0], '-', 1, 0);
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getPort() 
	{
		try {
			
			$query = Database::rows(
				$this->db_live,
				"SELECT Port
				FROM EmailLists
				WHERE ProjectID = 33 
				AND Port IS NOT NULL
				GROUP BY Port"
			);

			$port_array=[];

			foreach ($query as $key => $value) {
				$port_array[] = "'".$value['Port']."'";
			}

			$port_str = implode(",", $port_array);

			return $port_str;

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function mapQuantation($port,$quantation,$vouchercutstr)
	{
		try {
			return Database::hasRows(
				$this->db_ax,
				"SELECT SQ.custaccount
				,SQ.quotationid
				,SQ.DATAAREAID
				,SQ.CREATEDDATE
				,SO.DSG_TOPORTID [CONDITION]
				FROM SalesQuotationTable SQ
				JOIN SALESTABLE SO
				ON SO.QUOTATIONID = SQ.QUOTATIONID
				AND SO.DATAAREAID = SQ.DATAAREAID
				JOIN CustPackingSlipJour CJ
				ON CJ.SALESID = SO.SALESID
				AND CJ.DATAAREAID = SO.DATAAREAID
				WHERE SQ.DATAAREAID = 'dsc'
				AND SQ.CUSTACCOUNT IN ('C-1089','C-2759')
				AND SQ.quotationid = ?
				AND CJ.VOUCHER_NO = ?
				AND SO.DSG_TOPORTID in($port)
				ORDER BY  SQ.CREATEDDATE DESC",
				[$quantation, $vouchercutstr]
			);
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function mapQuantationdata($quantation,$voucher)
	{
		try {
			
			return Database::rows(
				$this->db_ax,
				"SELECT SQ.custaccount
				,SQ.quotationid
				,SQ.DATAAREAID
				,SQ.CREATEDDATE
				,SO.DSG_TOPORTID [CONDITION]
				FROM SalesQuotationTable SQ
				JOIN SALESTABLE SO
				ON SO.QUOTATIONID = SQ.QUOTATIONID
				AND SO.DATAAREAID = SQ.DATAAREAID
				JOIN CustPackingSlipJour CJ
				ON CJ.SALESID = SO.SALESID
				AND CJ.DATAAREAID = SO.DATAAREAID
				WHERE SQ.DATAAREAID = 'dsc'
				AND SQ.CUSTACCOUNT IN ('C-1089','C-2759')
				AND SQ.quotationid = ?
				AND CJ.VOUCHER_NO = ?
				ORDER BY  SQ.CREATEDDATE DESC",
				[$quantation, $voucher]
			);

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getShippingSubjectV2($file) 
	{
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

	public function isDateNull($date) 
	{
		try {
			if ( date('Y-m-d', strtotime($date)) === '1900-01-01' || date('Y-m-d', strtotime($date)) === '1970-01-01') {
				return '-';
			} else {
				return date('d M Y', strtotime($date));
			}
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getAgent($so, $customer) 
	{
		try {
			$res = Database::rows(
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

			if (count($res) === 0) {
				return '-';
			} else {
				return $res[0]['Agent'];
			}

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getShippingLine($so, $customer) 
	{
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

			if (count($res) === 0) {
				return '-';
			} else {
				if ($res[0]['ShippingLine'] === '') {
					return '-';
				} 
				return $res[0]['ShippingLine'];
			}
		} catch (\Exception $e) {
			return $e->getMessage();
		}
		
	}

	public function getShippingBodyV2($file) 
	{

		try {
			
			preg_match('/INV.*?(\\d+)/i', $file, $matched_inv);

			if (count($matched_inv) === 0) {
				$inv = '-';
			} 

			$custcode = self::getCustomerCode($file);

			if (strlen($custcode) === 0) {
				$_customerCode = '-';
			} else {
				$_customerCode = $custcode;
			}
			
			$getData = Database::rows(
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
				LEFT JOIN CustConfirmJour CC ON ST.CUSTACCOUNT = CC.INVOICEACCOUNT
				LEFT JOIN CUSTTABLE CT ON CT.ACCOUNTNUM = ST.CUSTACCOUNT AND CT.DATAAREAID = 'dsc'
				LEFT JOIN CustPackingSlipJour CPS ON ST.SALESID = CPS.SALESID AND CPS.DATAAREAID = 'dsc'
				WHERE ST.SALESID = CC.SALESID
				AND ST.CUSTACCOUNT = ?
				AND ST.DATAAREAID = 'dsc'
				AND CC.DSG_VoucherNo = ?
				AND CPS.SalesId = ST.SALESID
				ORDER BY CPS.PackingSlipId DESC",
				[
					$_customerCode,
					substr($matched_inv[0], 3)
				]
			);

			if (count($getData) === 0) {
				return '-';
			}

			$txt = '';
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

	public function getShippingDOCSInName($files = [])
	{
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
	
	public function getDataLoadingPlan()
	{
		try {
			
			return Database::rows(
				$this->db_ax,
				"SELECT	DSG_AMOUNTREQUIRE
						,CUSTACCOUNT
						,NAME
						,SALESID
						,QUOTATIONID
						,DSG_TOPORTID
						,DSG_TOPORTDESC
						,STD20
						,STD40
						,HC40
						,LCL
						,HC45
						,DSG_AVAILABLEDATE
						,DSG_PaymDateRequire
						,DSG_AGENT
						,DSG_RefCreatePurchId 
						,DSG_REQUESTSHIPDATE
						,DSG_NoteAutoMail
						,CurrencyCode
						,CURRENCYCODEISO
						,Payment
						,DSG_TERMGROUPID
						,PaymentType
						,CustomerRef
						,CASE WHEN Lastshipment IS NULL THEN ''
							  WHEN Lastshipment ='1900-01-01 00:00:00.000' THEN ''
						ELSE DATENAME(day,Lastshipment)+' '+DATENAME(month,Lastshipment)+' '+CONVERT(VARCHAR,YEAR(Lastshipment)) END [Lastshipment]
						,CASE WHEN Expirydate IS NULL THEN ''
							  WHEN Expirydate ='1900-01-01 00:00:00.000' THEN ''
						ELSE DATENAME(day,Expirydate)+' '+DATENAME(month,Expirydate)+' '+CONVERT(VARCHAR,YEAR(Expirydate)) END [Expirydate]
				FROM (
					SELECT 
						S.CUSTACCOUNT
						,C.NAME
						,S.SALESID
						,S.QUOTATIONID
						,S.DSG_TOPORTID
						,S.DSG_TOPORTDESC
						,CASE 
						WHEN S.DSG_CONTAINER1X20!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_CONTAINER1X20),0)+' x20'+'''STD'
						ELSE '' END [STD20]
						,CASE 
						WHEN S.DSG_CONTAINER1X40!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_CONTAINER1X40),0)+' x40'+'''STD'
						ELSE '' END [STD40]
						,CASE 
						WHEN S.DSG_CONTAINER1X40HC!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_CONTAINER1X40HC),0)+' x40'+'''HC'
						ELSE '' END [HC40]
						,CASE 
						WHEN S.DSG_ContainerLCL!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_ContainerLCL),0)+' LCL'
						ELSE '' END [LCL]
						,CASE 
						WHEN S.DSG_CONTAINER1X45HC!=0 THEN CONVERT(NVARCHAR,CONVERT(INT,S.DSG_CONTAINER1X45HC),0)+' x45'+'''HC'
						ELSE '' END [HC45]
						,CASE WHEN S.DSG_AVAILABLEDATE IS NULL THEN ''
							  WHEN S.DSG_AVAILABLEDATE ='1900-01-01 00:00:00.000' THEN ''
						ELSE DATENAME(day,S.DSG_AVAILABLEDATE)+' '+DATENAME(month,S.DSG_AVAILABLEDATE)+' '+CONVERT(VARCHAR,YEAR(S.DSG_AVAILABLEDATE)) END [DSG_AVAILABLEDATE]
						,CASE WHEN S.DSG_PaymDateRequire IS NULL THEN ''
							  WHEN S.DSG_PaymDateRequire ='1900-01-01 00:00:00.000' THEN ''
						ELSE DATENAME(day,S.DSG_PaymDateRequire)+' '+DATENAME(month,S.DSG_PaymDateRequire)+' '+CONVERT(VARCHAR,YEAR(S.DSG_PaymDateRequire)) END [DSG_PaymDateRequire]
						,ISNULL(S.DSG_AMOUNTREQUIRE,'')[DSG_AMOUNTREQUIRE]
						,CASE WHEN S.DSG_PRIMARYAGENTID = '' THEN 'Please advise agent for arrange booking as soon as possible.'
						ELSE A.DSG_DESCRIPTION END [DSG_AGENT]
						,S.DSG_RefCreatePurchId 
						,S.DSG_REQUESTSHIPDATE
						,S.DSG_NoteAutoMail
						,S.CurrencyCode
						,Y.CURRENCYCODEISO
						,S.Payment
						,PT.DSG_TERMGROUPID
						,SUBSTRING(PT.DSG_TERMGROUPID,1,2)[PaymentType]
						,S.CustomerRef
						,DATEADD(day, 20, S.DSG_AVAILABLEDATE) [Lastshipment]
						,DATEADD(day, 30, S.DSG_AVAILABLEDATE) [Expirydate]
					FROM SALESTABLE S 
					LEFT JOIN CUSTTABLE C ON S.CUSTACCOUNT = C. ACCOUNTNUM AND C.DATAAREAID = 'DSC'
					LEFT JOIN DSG_AgentTable A ON S.DSG_PRIMARYAGENTID = A.DSG_AGENTID AND A.DATAAREAID = 'DSC'
					LEFT JOIN CURRENCY Y ON S.CurrencyCode=Y.CURRENCYCODE AND Y.DATAAREAID = 'DSC'
					LEFT JOIN PaymTerm PT ON C.PAYMTERMID=PT.PAYMTERMID AND PT.DATAAREAID='DSC'
					WHERE S.DSG_AutoMailStatus = 1
					AND S.DATAAREAID = 'dsc'
					AND S.CUSTACCOUNT = 'C-1089'
				) Z
				GROUP BY
				Z.DSG_AmountRequire
				,Z.CUSTACCOUNT
				,Z.NAME
				,Z.SALESID
				,Z.QUOTATIONID
				,Z.DSG_TOPORTID
				,Z.DSG_TOPORTDESC
				,Z.STD20
				,Z.STD40
				,Z.HC40
				,Z.LCL
				,Z.HC45
				,Z.DSG_AVAILABLEDATE
				,Z.DSG_PaymDateRequire
				,Z.DSG_AGENT
				,Z.DSG_RefCreatePurchId 
				,Z.DSG_REQUESTSHIPDATE
				,Z.DSG_NoteAutoMail
				,Z.CurrencyCode
				,Z.CURRENCYCODEISO
				,Z.Payment
				,Z.DSG_TERMGROUPID
				,Z.PaymentType
				,Z.CustomerRef
				,Z.Lastshipment
				,Z.Expirydate"
			);

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function loadingplandata($salesid)
	{
		try {
					
			return Database::rows(
				$this->db_ax,
				"SELECT ST.SALESID,ST.DSG_TOPORTID [CONDITION] 
				FROM SALESTABLE ST
				WHERE ST.SALESID = ?
				AND ST.CUSTACCOUNT = 'C-1089'
				AND ST.DATAAREAID = 'dsc'",[$salesid]
			);

			return $query;

		} catch (\Exception $e) {
			$e->getMessage();
		}
	}

	public function getVesselData($time_set) 
	{
		$sql = "SELECT 
			SL.DSG_SALESID,
			SL.DSG_PACKINGSLIPID,
			ST.CUSTACCOUNT,
			ST.SALESNAME,
			ST.DSG_TOPORTID [CONDITION],
			(
				SELECT TOP 1 SLL.DSG_BEFOREVALUE FROM DSG_SALESLOG SLL 
				WHERE SLL.DSG_SALESID = SL.DSG_SALESID
				AND SLL.DSG_SALESLOGCATEGORY = 16
				ORDER BY SLL.CREATEDDATE DESC
			) [BEFORE_VESSEL],
			CJ.DSG_VESSEL [AFTER_VESSEL],
			(
				SELECT TOP 1 SLL.DSG_BEFOREVALUE FROM DSG_SALESLOG SLL 
				WHERE SLL.DSG_SALESID = SL.DSG_SALESID
				AND SLL.DSG_SALESLOGCATEGORY = 15
				ORDER BY SLL.CREATEDDATE DESC
			) [BEFORE_FEEDER],
			CJ.DSG_FEEDER [AFTER_FEEDER],
			(
				SELECT TOP 1 SLL.DSG_BEFOREVALUE FROM DSG_SALESLOG SLL 
				WHERE SLL.DSG_SALESID = SL.DSG_SALESID
				AND SLL.DSG_SALESLOGCATEGORY = 13
				ORDER BY SLL.CREATEDDATE DESC
			) [BEFORE_ETD],
			CONVERT(varchar, CJ.DSG_ETD, 23) [AFTER_ETD],
			(
				SELECT TOP 1 SLL.DSG_BEFOREVALUE FROM DSG_SALESLOG SLL 
				WHERE SLL.DSG_SALESID = SL.DSG_SALESID
				AND SLL.DSG_SALESLOGCATEGORY = 14
				ORDER BY SLL.CREATEDDATE DESC
			) [BEFORE_ETA],
			CONVERT(varchar, CJ.DSG_ETA, 23) [AFTER_ETA],
			CT.NAME [CUSTNAME],
			ST.QUOTATIONID,
			ST.CUSTOMERREF,
			ST.DSG_ToPortDesc [TOPORT],
			CASE WHEN CJ.DATAAREAID = 'DSR' THEN 'SVO/'+CJ.SERIES +'/' + CONVERT(NVARCHAR(10),CJ.VOUCHER_NO)ELSE  UPPER(CJ.DATAAREAID) + '/'+ CJ.SERIES +'/' + CONVERT(NVARCHAR(10),CJ.VOUCHER_NO)  END [INVNO]
			FROM DSG_SALESLOG SL
			LEFT JOIN SALESTABLE ST ON 
				SL.DSG_SALESID = ST.SALESID 
				AND ST.DATAAREAID = 'dsc'
			LEFT JOIN CustPackingSlipJour CJ ON 
				CJ.SALESID = SL.DSG_SALESID 
				AND CJ.DATAAREAID = 'dsc'
				AND CJ.INVOICEACCOUNT = ST.CUSTACCOUNT
				AND SL.DSG_PACKINGSLIPID = CJ.PACKINGSLIPID
			LEFT JOIN CUSTTABLE CT ON 
				CT.ACCOUNTNUM = ST.CUSTACCOUNT 
				AND CT.DATAAREAID = 'dsc'
			WHERE SL.CREATEDDATE >= ?
			AND SL.CREATEDDATE <= ?
			AND CONVERT(time, dateadd(s, SL.CREATEDTIME , '19700101')) >= ?
			AND CONVERT(time, dateadd(s, SL.CREATEDTIME , '19700101')) <= ?
			AND SL.DSG_DATAAREAID = 'dsc'
			AND SL.DSG_SALESLOGCATEGORY IN (13,14,15,16)
			AND ST.CUSTACCOUNT = 'C-1089'
			GROUP BY
			SL.DSG_SALESID,
			SL.DSG_PACKINGSLIPID,
			ST.SALESNAME,
			ST.CUSTACCOUNT,
			ST.DSG_TOPORTID,
			CJ.DSG_VESSEL,
			CJ.DSG_FEEDER,
			CT.NAME,
			ST.QUOTATIONID,
			ST.CUSTOMERREF,
			ST.DSG_ToPortDesc,
			CJ.DSG_ETD,
			CJ.DSG_ETA,
			CASE WHEN CJ.DATAAREAID = 'DSR' THEN 'SVO/'+CJ.SERIES +'/' + CONVERT(NVARCHAR(10),CJ.VOUCHER_NO)ELSE  UPPER(CJ.DATAAREAID) + '/'+ CJ.SERIES +'/' + CONVERT(NVARCHAR(10),CJ.VOUCHER_NO)  END";

		$query = Database::rows(
			$this->db_ax,
			$sql,
			[
				$time_set['start_date'],
				$time_set['end_date'],
				$time_set['start_time'],
				$time_set['end_time']
			]
		);

		return $query;
	}	

	public function getVesselSubject($qa, $inv, $custname) {
		try {
			$txt = 'Vesssel info update : ' . $qa . ', ' . $inv . ', ' . $custname;
			return $txt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getVesselBody(
		$custname,
		$qa,
		$po,
		$to_port,
		$before_etd,
		$after_etd,
		$before_eta,
		$after_eta,
		$inv,
		$so,
		$before_vessel,
		$before_feeder,
		$after_vessel,
		$after_feeder
	) 
	{

		try {
			$countNoted = 0;

			$txt = '';

			$txt .= '<b>Customer name : </b>'.$custname.'<br>';
			$txt .= '<b>SO : </b>'.self::getSoCombined($so).'<br>';
			$txt .= '<b>PI ID : </b>'.self::getPICombined($so).'<br>';
			$txt .= '<b>PO : </b>'.$po.'<br>';
			$txt .= '<b>Destination port : </b>'.$to_port.'<br>';
			$txt .= '<b>ETD : </b>'.$after_etd.'<br>';
			$txt .= '<b>ETA : </b>'.$after_eta.'<br>';
			$txt .= '<b>Mother Vessel & V. : </b>'.$after_vessel.'<br>';
			$txt .= '<b>Feeder Vessel & V. : </b>'.$after_feeder.'<br>';
			$txt .= '<b>Invoice No : </b>'.$inv.'<br><br>';

			if ( ( $before_etd !== $after_etd && $before_etd !== null && $after_etd !== null ) || 
				 ( $before_eta !== $after_eta && $before_eta !== null && $after_eta !== null ) || 
				 ( $before_vessel !== $after_vessel && $before_vessel !== null && $after_vessel !== null ) || 
				 ( $before_feeder !== $after_feeder && $before_feeder !== null && $after_feeder !== null ) )  {
				$txt .= '<b>** NOTED **</b> <br>';
			}
			
			if ($before_etd !== $after_etd && $before_etd !== null && $after_etd !== null) {

				if ($before_etd === '') {
					$b_etd_ = 'n/a';
				} else {
					$b_etd_ = $before_etd;
				}

				if ($after_etd === '') {
					$a_etd_ = 'n/a';
				} else {
					$a_etd_ = $after_etd;
				}

				$txt .= 'ETD Changed from '.$b_etd_.' to '.$a_etd_.' <br>';
			} 
			
			if ($before_eta !== $after_eta && $before_eta !== null && $after_eta !== null) {

				if ($before_eta === '') {
					$b_eta_ = 'n/a';
				} else {
					$b_eta_ = $before_eta;
				}

				if ($after_eta === '') {
					$a_eta_ = 'n/a';
				} else {
					$a_eta_ = $after_eta;
				}

				$txt .= 'ETA Changed from '.$b_eta_.' to '.$a_eta_.' <br>';
			}

			if ($before_vessel !== $after_vessel && $before_vessel !== null && $after_vessel !== null) {
				
				if ($before_vessel === '') {
					$before_vessel = 'n/a';
				}

				if ($after_vessel === '') {
					$after_vessel = 'n/a';
				}

				$txt .= 'Mother Vessel Changed from ' . $before_vessel . ' to '.$after_vessel.' <br>';
			}
			
			if ($before_feeder !== $after_feeder && $before_feeder !== null && $after_feeder !== null) {

				if ($before_feeder === '') {
					$before_feeder = 'n/a';
				}

				if ($after_feeder === '') {
					$after_feeder = 'n/a';
				}

				$txt .= 'Feeder Vessel Changed from ' . $before_feeder . ' to '.$after_feeder.' <br>';
			}

			return $txt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getSoCombined($so)
	{
		try {
			$data = Database::rows(
				$this->db_ax,
				"SELECT
					CSL.SALESID [SO_PARENT],
					S.SALESID [SO_CHILD],
					S.QUOTATIONID [PI]
					FROM SALESTABLE S
					LEFT JOIN(
						SELECT 
						MAX(CONFIRMID)CONFIRMID,
						DATAAREAID,
						ORIGSALESID
						FROM CUSTCONFIRMSALESLINK SL
						GROUP BY DATAAREAID, ORIGSALESID
					) SL
					ON SL.DATAAREAID = S.DATAAREAID
					AND SL.ORIGSALESID = S.SALESID
					JOIN CUSTCONFIRMSALESLINK CSL
					ON CSL.DATAAREAID = S.DATAAREAID
					AND CSL.ORIGSALESID = S.SALESID
					AND CSL.CONFIRMID = SL.CONFIRMID
					WHERE S.DATAAREAID = 'dsc'
					AND CSL.SALESID = ?",
					[
						$so
					]
			);

			$txt = '';

			if ( count($data) === 0 ) {
				return $so;
			}

			foreach ($data as $v) {
				$txt .= $v['SO_CHILD'] . ', ';
			}

			return trim($txt, ', ');
		} catch (\Exception $e) {
			$e->getMessage();
		}
	}

	public function getPICombined($so)
	{
		try {
			$data = Database::rows(
				$this->db_ax,
				"SELECT
					S.QUOTATIONID [PI]
					FROM SALESTABLE S
					LEFT JOIN(
						SELECT 
						MAX(CONFIRMID)CONFIRMID,
						DATAAREAID,
						ORIGSALESID
						FROM CUSTCONFIRMSALESLINK SL
						GROUP BY DATAAREAID, ORIGSALESID
					) SL
					ON SL.DATAAREAID = S.DATAAREAID
					AND SL.ORIGSALESID = S.SALESID
					JOIN CUSTCONFIRMSALESLINK CSL
					ON CSL.DATAAREAID = S.DATAAREAID
					AND CSL.ORIGSALESID = S.SALESID
					AND CSL.CONFIRMID = SL.CONFIRMID
					WHERE S.DATAAREAID = 'dsc'
					AND CSL.SALESID = ?
					GROUP BY S.QUOTATIONID",
					[
						$so
					]
			);

			$txt = '';

			if ( count($data) === 0 ) {
				return '-';
			}

			foreach ($data as $v) {
				$txt .= $v['PI'] . ', ';
			}

			return trim($txt, ', ');
		} catch (\Exception $e) {
			$e->getMessage();
		}
	}

	public function getAirWayBillData($time_set)
	{
		try {
			$sql = "SELECT TOP 1
				SL.DSG_SALESID,
				SL.DSG_PACKINGSLIPID,
				ST.CUSTACCOUNT,
				ST.SALESNAME,
				ST.DSG_SHIPPINGLINEDESCRIPTION [SHIPPINGLINE],
				ST.DSG_TOPORTID [CONDITION],
				(
					SELECT TOP 1 SLL.DSG_AFTERVALUE FROM DSG_SALESLOG SLL 
					WHERE SLL.DSG_SALESID = SL.DSG_SALESID
					AND SLL.DSG_SALESLOGCATEGORY = 17
					ORDER BY SLL.CREATEDDATE DESC
				) [AFTERVALUE],
				CT.NAME [CUSTNAME],
				ST.QUOTATIONID,
				ST.CUSTOMERREF,
				ST.DSG_ToPortDesc [TOPORT],
				DA.DSG_DESCRIPTION [AGENT],
				KP.DSG_COURIERNAME,
				CJ.DSG_AWB_NO,
				CJ.DSG_ETD,
				CJ.DSG_ETA,
				CASE WHEN IV.DATAAREAID = 'DSR' THEN 'SVO/'+IV.SERIES +'/' + CONVERT(NVARCHAR(10),IV.VOUCHER_NO)ELSE  UPPER(IV.DATAAREAID) + '/'+ IV.SERIES +'/' + CONVERT(NVARCHAR(10),IV.VOUCHER_NO)  END [INVNO]
				FROM DSG_SALESLOG SL
				LEFT JOIN SALESTABLE ST ON 
					SL.DSG_SALESID = ST.SALESID 
					AND ST.DATAAREAID = 'dsc'
				LEFT JOIN CustPackingSlipJour CJ ON 
					CJ.SALESID = SL.DSG_SALESID 
					AND CJ.DATAAREAID = 'dsc'
					AND CJ.INVOICEACCOUNT = ST.CUSTACCOUNT
					AND SL.DSG_PACKINGSLIPID = CJ.PACKINGSLIPID
				LEFT JOIN INVENTPICKINGLISTJOUR IV ON
					IV.ORDERID = SL.DSG_SALESID
					AND IV.DATAAREAID = 'dsc'
					AND IV.CUSTACCOUNT = ST.CUSTACCOUNT
				LEFT JOIN DSG_AGENTTABLE DA ON
					DA.DSG_AGENTID = ST.DSG_PRIMARYAGENTID
				LEFT JOIN CUSTTABLE CT ON 
					CT.ACCOUNTNUM = ST.CUSTACCOUNT 
					AND CT.DATAAREAID = 'dsc'
				LEFT JOIN DSG_KPICOURIER KP ON
					KP.DSG_COURIERID = CJ.DSG_COURIERID
				WHERE SL.CREATEDDATE >= ?
				AND SL.CREATEDDATE <= ?
				AND CONVERT(time, dateadd(s, SL.CREATEDTIME , '19700101')) >= ?
				AND CONVERT(time, dateadd(s, SL.CREATEDTIME , '19700101')) <= ?
				AND SL.DSG_DATAAREAID = 'dsc'
				-- AND SL.DSG_SALESLOGCATEGORY = '17'
				AND ST.DLVMODE = 'SHIP'
				AND CJ.DSG_AIRWAYBILLSENTEMAIL = '0' 
				AND ST.CUSTACCOUNT = 'C-1089'
				GROUP BY
				SL.DSG_SALESID,
				SL.DSG_PACKINGSLIPID,
				ST.SALESNAME,
				ST.DSG_SHIPPINGLINEDESCRIPTION,
				ST.CUSTACCOUNT,
				ST.DSG_TOPORTID,
				CJ.DSG_VESSEL,
				CJ.DSG_FEEDER,
				CT.NAME,
				ST.QUOTATIONID,
				ST.CUSTOMERREF,
				ST.DSG_ToPortDesc,
				DA.DSG_DESCRIPTION,
				KP.DSG_COURIERNAME,
				CJ.DSG_AWB_NO,
				CJ.DSG_ETD,
				CJ.DSG_ETA,
				CASE WHEN IV.DATAAREAID = 'DSR' THEN 'SVO/'+IV.SERIES +'/' + CONVERT(NVARCHAR(10),IV.VOUCHER_NO)ELSE  UPPER(IV.DATAAREAID) + '/'+ IV.SERIES +'/' + CONVERT(NVARCHAR(10),IV.VOUCHER_NO)  END";

			$query = Database::rows(
				$this->db_ax,
				$sql,
				[
					$time_set['start_date'],
					$time_set['end_date'],
					$time_set['start_time'],
					$time_set['end_time']
				]
			);

			return $query;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getDocumentcheck($so)
	{
		try {
			return Database::hasRows(
				$this->db_ax,
				"SELECT AWB.LIST [DC]
				FROM DSG_AIRWAYBILL AWB
				WHERE AWB.SALESID = ? 
				AND AWB.DATAAREAID = 'dsc'
				AND AWB.ACTIVE = '1'",
				[
					$so
				]
			);
		} catch (\Exception $e) {
			$e->getMessage();
		}
	}

	public function AWBcheckSend($saleid)
	{
		try {
			return Database::hasRows(
				$this->db_live,
				"SELECT TOP 1 SALEID 
				FROM [AUTOMAIL_EXPORT].[dbo].[LOGAWB]
				WHERE SALEID = ?",
				[
					$saleid
				]
			);
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getAirWayBillSubject($pi, $inv, $custname, $type = 'new') 
	{
		try {
			
			$text = '';
			if ($type === 'revised') {
				$txt = 'REVISED AIR WAY BILL : ' . $pi . ', ' . $inv . ', ' . $custname;
			} else {
				$txt = 'AIR WAY BILL : ' . $pi . ', ' . $inv . ', ' . $custname;			
			}

			return $text;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getAirWayBillBody1(
		$via,
		$airnum,
		$custname,
		$quotation,
		$custref,
		$so,
		$dsg_etd,
		$dsg_eta,
		$inv,
		$to_port,
		$Agentde,
		$Shippingline,
		$cust
		
	) 
	{

		try {
		
			if ( trim($dsg_etd) === '' || 
				$dsg_etd === null ||
				$dsg_etd === '01/01/1970' ||
				$dsg_etd === '01/01/1900') {
				$dsg_etd = null;
			}

			if ( trim($dsg_eta) === '' || 
				$dsg_eta === null ||
				$dsg_eta === '01/01/1970' ||
				$dsg_eta === '01/01/1900') {
				$dsg_eta = null;
			}

			$txt = '';
			$txt .= 'Dear Sir / Madam <br><br>';
			$txt .= 'We are sending original document are below detail. <br><br>';
			$txt .= '<b>Document sending : </b>'.self::getDocument($so).'<br>';
			$txt .= '<b>Via courier : </b>'.$via.'<br>';
			$txt .= '<b>Air Way Bill number : </b>'.$airnum.'<br><br>';
			$txt .= '<b>Customer name : </b>'.$custname.'<br>';
			$txt .= '<b>PI ID : </b>'.'DSC-'.$quotation.'<br>';
			$txt .= '<b>PO : </b>'.$custref.'<br>';
			$txt .= '<b>SO ID : </b>'.$so.'<br>';
			$txt .= '<b>ETD : </b>'.$dsg_etd.'<br>';
			$txt .= '<b>ETA : </b>'.$dsg_eta.'<br>';
			$txt .= '<b>Invoice No : </b>'.$inv.'<br>';
			$txt .= '<b>Destination port : </b>'.$to_port.'<br>';
			$txt .= '<b>Agent : </b>'.$Agentde.'<br>';
			$txt .= '<b>Shipping Line : </b>'.$Shippingline.'<br><br><br><br>';

			$signature = self::getsignatureairwaybill($cust);

			$txt .= "<font size='2px' color='#696969'>Best regards, </font><br>";
			$txt .= "<font size='2px' color='#696969'>ECS department</font><br>";
			$txt .= "<font size='2px' color='#696969'><a href='www.deestone.com'>www.deestone.com</a></font><br>";
			$txt .= "<font size='2px' color='#696969'>Tel: ".$signature[0]['DSG_PHONE']."</font>";

			return $txt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}

	}

	public function getAirWayBillBody2(
		$via,
		$airnum,
		$custname,
		$quotation,
		$custref,
		$so,
		$dsg_etd,
		$dsg_eta,
		$inv,
		$to_port,
		$Agentde,
		$Shippingline,
		$cust
		
	) 
	{

		try {
			
			if ( trim($dsg_etd) === '' || 
				$dsg_etd === null ||
				$dsg_etd === '01/01/1970' ||
				$dsg_etd === '01/01/1900') {
				$dsg_etd = null;
			}

			if ( trim($dsg_eta) === '' || 
				$dsg_eta === null ||
				$dsg_eta === '01/01/1970' ||
				$dsg_eta === '01/01/1900') {
				$dsg_eta = null;
			}

			$txt = '';
			$txt .= 'Dear Sir / Madam <br><br>';
			$txt .= 'We are revised sending original document are below detail. <br><br>';
			$txt .= '<b>Document sending : </b>'.self::getDocument($so).'<br>';
			$txt .= '<b>Via courier : </b>'.$via.'<br>';
			$txt .= '<b>Air Way Bill number : </b>'.$airnum.'<br><br>';
			$txt .= '<b>Customer name : </b>'.$custname.'<br>';
			$txt .= '<b>PI ID : </b>'.'DSC-'.$quotation.'<br>';
			$txt .= '<b>PO : </b>'.$custref.'<br>';
			$txt .= '<b>SO ID : </b>'.$so.'<br>';
			$txt .= '<b>ETD : </b>'.$dsg_etd.'<br>';
			$txt .= '<b>ETA : </b>'.$dsg_eta.'<br>';
			$txt .= '<b>Invoice No : </b>'.$inv.'<br>';
			$txt .= '<b>Destination port : </b>'.$to_port.'<br>';
			$txt .= '<b>Agent : </b>'.$Agentde.'<br>';
			$txt .= '<b>Shipping Line : </b>'.$Shippingline.'<br><br><br><br>';
		
			$signature = self::getsignatureairwaybill($cust);

			$txt .= "<font size='2px' color='#696969'>Best regards, </font><br>";
			$txt .= "<font size='2px' color='#696969'>ECS department</font><br>";
			$txt .= "<font size='2px' color='#696969'><a href='www.deestone.com'>www.deestone.com</a></font><br>";
			$txt .= "<font size='2px' color='#696969'>Tel: ".$signature[0]['DSG_PHONE']."</font>";

			return $txt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}

	}

	public function getAirWayBillBody3(
		$airnum,
		$custname,
		$quotation,
		$custref,
		$so,
		$dsg_etd,
		$dsg_eta,
		$inv,
		$to_port,
		$Agentde,
		$Shippingline,
		$cust
		
	) 
	{
		
		try {
			if ( trim($dsg_etd) === '' || 
				$dsg_etd === null ||
				$dsg_etd === '01/01/1970' ||
				$dsg_etd === '01/01/1900') {
				$dsg_etd = null;
			}

			if ( trim($dsg_eta) === '' || 
				$dsg_eta === null ||
				$dsg_eta === '01/01/1970' ||
				$dsg_eta === '01/01/1900') {
				$dsg_eta = null;
			}

			$txt = '';
			$txt .= 'Dear Sir / Madam <br><br>';
			$txt .= 'We are sending original document are below detail. <br><br>';
			$txt .= '<b>Air Way Bill number : </b>'.$airnum.'<br><br>';
			$txt .= '<b>Customer name : </b>'.$custname.'<br>';
			$txt .= '<b>PI ID : </b>'.'DSC-'.$quotation.'<br>';
			$txt .= '<b>PO : </b>'.$custref.'<br>';
			$txt .= '<b>SO ID : </b>'.$so.'<br>';
			$txt .= '<b>ETD : </b>'.$dsg_etd.'<br>';
			$txt .= '<b>ETA : </b>'.$dsg_eta.'<br>';
			$txt .= '<b>Invoice No : </b>'.$inv.'<br>';
			$txt .= '<b>Destination port : </b>'.$to_port.'<br>';
			$txt .= '<b>Agent : </b>'.$Agentde.'<br>';
			$txt .= '<b>Shipping Line : </b>'.$Shippingline.'<br><br><br><br>';

			$signature = self::getsignatureairwaybill($cust);
			
			$txt .= "<font size='2px' color='#696969'>Best regards, </font><br>";
			$txt .= "<font size='2px' color='#696969'>ECS department</font><br>";
			$txt .= "<font size='2px' color='#696969'><a href='www.deestone.com'>www.deestone.com</a></font><br>";
			$txt .= "<font size='2px' color='#696969'>Tel: ".$signature[0]['DSG_PHONE']."</font>";

			return $txt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}

	}

	public function getAirWayBillBody4(
		$airnum,
		$custname,
		$quotation,
		$custref,
		$so,
		$dsg_etd,
		$dsg_eta,
		$inv,
		$to_port,
		$Agentde,
		$Shippingline,
		$cust
		
	) 
	{
		
		try {
			if ( trim($dsg_etd) === '' || 
				$dsg_etd === null ||
				$dsg_etd === '01/01/1970' ||
				$dsg_etd === '01/01/1900') {
				$dsg_etd = null;
			}

			if ( trim($dsg_eta) === '' || 
				$dsg_eta === null ||
				$dsg_eta === '01/01/1970' ||
				$dsg_eta === '01/01/1900') {
				$dsg_eta = null;
			}

			$txt = '';
			$txt .= 'Dear Sir / Madam <br><br>';
			$txt .= 'We are revised sending original document are below detail. <br><br>';
			$txt .= '<b>Air Way Bill number : </b>'.$airnum.'<br><br>';
			$txt .= '<b>Customer name : </b>'.$custname.'<br>';
			$txt .= '<b>PI ID : </b>'.'DSC-'.$quotation.'<br>';
			$txt .= '<b>PO : </b>'.$custref.'<br>';
			$txt .= '<b>SO ID : </b>'.$so.'<br>';
			$txt .= '<b>ETD : </b>'.$dsg_etd.'<br>';
			$txt .= '<b>ETA : </b>'.$dsg_eta.'<br>';
			$txt .= '<b>Invoice No : </b>'.$inv.'<br>';
			$txt .= '<b>Destination port : </b>'.$to_port.'<br>';
			$txt .= '<b>Agent : </b>'.$Agentde.'<br>';
			$txt .= '<b>Shipping Line : </b>'.$Shippingline.'<br><br><br><br>';

			$signature = self::getsignatureairwaybill($cust);

			$txt .= "<font size='2px' color='#696969'>Best regards, </font><br>";
			$txt .= "<font size='2px' color='#696969'>ECS department</font><br>";
			$txt .= "<font size='2px' color='#696969'><a href='www.deestone.com'>www.deestone.com</a></font><br>";
			$txt .= "<font size='2px' color='#696969'>Tel: ".$signature[0]['DSG_PHONE']."</font>";

			return $txt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
		
	}

	public function getDocument($so)
	{
		try {
			$data = Database::rows(
				$this->db_ax,
				"SELECT AWB.LIST [DC]
				FROM DSG_AIRWAYBILL AWB
				WHERE AWB.SALESID = ? 
				AND AWB.DATAAREAID = 'dsc'",
				[
					$so
				]
			);

			$txt = '';

			if ( count($data) === 0 ) {
				return '-';
			}

			foreach ($data as $v) {
				$txt .= $v['DC'] . ', ';
			}

			return trim($txt, ', ');
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getsignatureairwaybill($cust)
	{
		try {
			return Database::rows(
				$this->db_ax, 
				"SELECT TOP 1 DSG_ADDRESS,DSG_PHONE FROM DSG_CustomerEmailList E
				LEFT JOIN DSG_EditCustEmailList S ON E.EMAILID = S.DSG_EMAILID AND S.DATAAREAID = 'dsc'
				WHERE E.DSG_INACTIVE=0 AND E.DSG_SENDTYPE=3 AND E.DSG_ACCOUNTNUM=? AND E.DATAAREAID='dsc'",[$cust]
			);
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

}