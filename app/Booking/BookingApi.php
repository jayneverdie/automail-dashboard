<?php 

namespace App\Booking;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class BookingAPI {

	public function __construct() {
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
		$this->automail = new Automail;
	}

	public function isSOAndCustomerMatched($so, $customer) {
		try {
			return Database::hasRows(
				$this->db_ax,
				"SELECT SALESID FROM SALESTABLE 
				WHERE SALESID = ?
				AND CUSTACCOUNT = ?
				and CUSTGROUP = 'ovs'
				AND DATAAREAID = 'dsc'",
				[
					$so,
					$customer
				]
			);
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getCustNameFromSO($so, $customer) {
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

	public function getQABySO($so, $customer) {
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

	public function getSOFromFileBooking($filename) {
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

	public function getCustomerCode($file) {
		try {
			preg_match('/C.*?(\\d+)/i', $file, $data);
			return substr_replace($data[0], '-', 1, 0);
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getCustomerPOFromSO($so, $customer) {
		try {
			$res = Database::rows(
				$this->db_ax,
				"SELECT CUSTOMERREF 
				FROM Salestable 
				where CUSTOMERREF <> ''
				and CUSTGROUP = 'ovs'
				AND salesid = ?
				AND DATAAREAID = 'dsc'",
				[
					$so
				]
			);
			return $res[0]['CUSTOMERREF'];
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getETAFromSO($so, $customer) {
		try {
			$res = Database::rows(
				$this->db_ax,
				"SELECT DSG_ETADate 
				FROM Salestable 
				where salesid = ?
				AND custaccount = ?
				AND DATAAREAID = 'dsc'",
				[
					$so,
					$customer
				]
			);

			if (trim($res[0]['DSG_ETADate']) === '' || 
					$res[0]['DSG_ETADate'] === null) {
				return '-';
			}

			if ( date('Y-m-d', strtotime($res[0]['DSG_ETADate'])) === '1900-01-01' || 
			date('Y-m-d', strtotime($res[0]['DSG_ETADate'])) === '1970-01-01') {
				return '-';
			}

			return date('d/m/Y', strtotime($res[0]['DSG_ETADate']));
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function isToBeAdvise($so, $customer) {
		try {
			$res = Database::rows(
				$this->db_ax,
				"SELECT st.DSG_TobeAdvise FROM SALESTABLE st
				WHERE st.salesid = ?
				AND st.CUSTACCOUNT = ?
				AND st.DATAAREAID = 'dsc'",
				[
					$so,
					$customer
				]
			);
			return $res;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function arrayToString($arrayString) {
		try {
			$text = '';
			foreach ($arrayString as $data) {
				$text .= $data . ', ';
			}
			
			return trim($text, ', ');
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getDestinationPortFromSO($so, $customer) {
		try {
			$res = Database::rows(
				$this->db_ax,
				"SELECT DSG_ToPortDesc 
				FROM Salestable 
				where salesid = ?
				AND custaccount = ?
				AND DATAAREAID = 'dsc'",
				[
					$so,
					$customer
				]
			);

			if ( count($res) === 0 ) {
				return '';
			}

			return $res[0]['DSG_ToPortDesc'];
		} catch (\Exception $e) {
			$e->getMessage();
		}
	}

	public function getEDDFromSO($so, $customer) {
		try {
			$res = Database::rows(
				$this->db_ax,
				"SELECT DSG_EDDDate 
				FROM Salestable 
				where salesid = ?
				AND custaccount = ?
				AND DATAAREAID = 'dsc'",
				[
					$so,
					$customer
				]
			);

			if (trim($res[0]['DSG_EDDDate']) === '' || 
					$res[0]['DSG_EDDDate'] === null) {
				return '-';
			}

			if ( date('Y-m-d', strtotime($res[0]['DSG_EDDDate'])) === '1900-01-01' || 
			date('Y-m-d', strtotime($res[0]['DSG_EDDDate'])) === '1970-01-01') {
				return '-';
			}

			return date('d/m/Y', strtotime($res[0]['DSG_EDDDate']));	
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getETDFromSO($so, $customer) {
		try {
			$res = Database::rows(
				$this->db_ax,
				"SELECT DSG_ETDDate 
				FROM Salestable 
				where salesid = ?
				AND custaccount = ?
				AND DATAAREAID = 'dsc'",
				[
					$so,
					$customer
				]
			);

			if (trim($res[0]['DSG_ETDDate']) === '' || 
					$res[0]['DSG_ETDDate'] === null) {
				return '-';
			}

			if ( date('Y-m-d', strtotime($res[0]['DSG_ETDDate'])) === '1900-01-01' || 
			date('Y-m-d', strtotime($res[0]['DSG_ETDDate'])) === '1970-01-01') {
				return '-';
			}
			
			return date('d/m/Y', strtotime($res[0]['DSG_ETDDate']));
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getContainerFromSO($so, $customer) {
		try {
			$text  = '';

			if ( count($so) === 1 ) {

				$res = Database::rows(
					$this->db_ax,
					"SELECT
					DSG_Container1X20 [c1],
					DSG_Container1X40 [c2],
					DSG_Container1X40HC [c3],
					DSG_ContainerLCL [c4],
					DSG_Container1X45HC [c5]
					from Salestable 
					where  custaccount = ?
					and SALESID = ? 
					and dataareaid = 'dsc'",
					[
						$customer,
						$so
					]
				);

				if ( (int)$res[0]['c1'] !== 0 ) {
					$text .= (int)$res[0]['c1'] . 'x20 STD & ';
				} 
				
				if ( (int)$res[0]['c2'] !== 0 ) {
					$text .= (int)$res[0]['c2'] . 'x40 STD & ';
				}

				if ( (int)$res[0]['c3'] !== 0 ) {
					$text .= (int)$res[0]['c3'] . 'x40\'HC & ';
				}

				if ( (int)$res[0]['c4'] !== 0 ) {
					$text .= (int)$res[0]['c4'] . 'xLCL & ';
				}

				if ( (int)$res[0]['c5'] !== 0 ) {
					$text .= (int)$res[0]['c5'] . 'x45\'HC & ';
				}

				return trim($text, '& ');
				

			}else if ( count($so) > 1 ) {
				
				$containerQty['c1'] = 0;
				$containerQty['c2'] = 0;
				$containerQty['c3'] = 0;
				$containerQty['c4'] = 0;
				$containerQty['c5'] = 0;

				foreach ($so as $v) {
					$res_2 = Database::rows(
						$this->db_ax,
						"SELECT
						DSG_Container1X20 [c1],
						DSG_Container1X40 [c2],
						DSG_Container1X40HC [c3],
						DSG_ContainerLCL [c4],
						DSG_Container1X45HC [c5]
						from Salestable 
						where  custaccount = ?
						and SALESID = ? 
						and dataareaid = 'dsc'",
						[
							$customer,
							$v
						]
					);

					if ((int)$res_2[0]['c1'] !== 0)
						$containerQty['c1'] += (int)$res_2[0]['c1'];
					
					if ((int)$res_2[0]['c2'] !== 0)
						$containerQty['c2'] += (int)$res_2[0]['c2'];

					if ((int)$res_2[0]['c3'] !== 0)
						$containerQty['c3'] += (int)$res_2[0]['c3'];
					
					if ((int)$res_2[0]['c4'] !== 0)
						$containerQty['c4'] += (int)$res_2[0]['c4'];

					if ((int)$res_2[0]['c5'] !== 0)
						$containerQty['c5'] += (int)$res_2[0]['c5'];
				}

				if ($containerQty['c1'] !== 0)
					$text .= $containerQty['c1'] . 'x20 STD & ';
				
				if ($containerQty['c2'] !== 0)
					$text .= $containerQty['c2'] . 'x40 STD & ';

				if ($containerQty['c3'] !== 0)
					$text .= $containerQty['c3'] . 'x40\'HC & ';
				
				if ($containerQty['c4'] !== 0)
					$text .= $containerQty['c4'] . 'xLCL & ';

				if ($containerQty['c5'] !== 0)
					$text .= $containerQty['c5'] . 'x45\'HC & ';

				return  trim($text, '& ');
			}

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getAgent($so, $customer) {
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

	public function getBookingSubject_v2($file, $type = 'new') {
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

	public function getBookingBody_v2($file, $type = 'new') {
		try {
			$text = '';
		
			$so = self::getSOFromFileBooking($file);
			$customer = self::getCustomerCode($file);

			if (count($so) === 0) {
				return '';
			}

			$txtPI = '';
			$txtPO = '';
			$tempTextPi = [];

			foreach ($so as $v) {
				
				if ( !in_array( $v, $tempTextPi ) ) {
					$txtPI .= self::getQABySO($v, $customer) . ', ';
					$tempTextPi[] = $v;
				}
			}

			foreach ($so as $v2) {
				$txtPO .= self::getCustomerPOFromSO($v2, $customer) . ', ';
			}

			$txtETA = self::getETAFromSO($so[0], $customer);
			$txtETA_plan2 = '';

			if ( trim($txtETA) === '-' ) {
				$tempTobeAdvise = self::isToBeAdvise($so[0], $customer);
				if ( (int)$tempTobeAdvise === 1 ) {
					$txtETA_plan2 = 'To be advised';
				} else {
					$txtETA_plan2 = $txtETA;
				}
			} else {
				$txtETA_plan2 = $txtETA;
			}

			$text .= 'Dear Sir / Madam, <br><br>';

	    	$text .= 'Please find the attached ' . $type . ' booking confirmation.<br><br>';
			$text .= '<b>Customer name : </b>' . self::getCustNameFromSO($so[0], $customer) . '<br>';
			$text .= '<b>SO : </b>' . self::arrayToString($so) . '<br>';
			$text .= '<b>P/I : </b>' . trim($txtPI, ', ') . '<br>';
			$text .= '<b>PO : </b>' . trim($txtPO, ', ') . '<br>';
			$text .= '<b>Destination port : </b>'. self::getDestinationPortFromSO($so[0], $customer) . '<br>';
	    	$text .= '<b>Loading date : </b>'. self::getEDDFromSO($so[0], $customer) . '<br>';
			$text .= '<b>ETD : </b>'. self::getETDFromSO($so[0], $customer) . '<br>';
			$text .= '<b>ETA : </b>'. $txtETA_plan2 .  '<br>';
			$text .= '<b>Container : </b>'. self::getContainerFromSO($so, $customer) . '<br>';
			$text .= '<b>Agent : </b>'. self::getAgent($so[0], $customer).  '<br>';
			$text .= '<b>Shipping Line : </b>'. self::getShippingLine($so[0], $customer);
			
			return $text;	
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getQAByBOOKINGNUMBER($so, $customer) {

		$res = Database::rows(
			$this->db_ax,
			"SELECT DSG_BOOKINGNUMBER
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
			return $res[0]['DSG_BOOKINGNUMBER'];
		} else {
			return '';
		}
	}

	public function getQAByCY($so, $customer) {
		
		$res = Database::rows(
			$this->db_ax,
			"SELECT DSG_CY
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

		if (trim($res[0]['DSG_CY']) === '' || 
				$res[0]['DSG_CY'] === null) {
			return '-';
		}

		if ( date('Y-m-d', strtotime($res[0]['DSG_CY'])) === '1900-01-01' || 
		date('Y-m-d', strtotime($res[0]['DSG_CY'])) === '1970-01-01') {
			return '-';
		}

		return date('d/m/Y', strtotime($res[0]['DSG_CY']));
	}

	public function getQAByRTN($so, $customer) {
		
		$res = Database::rows(
			$this->db_ax,
			"SELECT DSG_RTN
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

		if (trim($res[0]['DSG_RTN']) === '' || 
				$res[0]['DSG_RTN'] === null) {
			return '-';
		}

		if ( date('Y-m-d', strtotime($res[0]['DSG_RTN'])) === '1900-01-01' || 
		date('Y-m-d', strtotime($res[0]['DSG_RTN'])) === '1970-01-01') {
			return '-';
		}

		return date('d/m/Y', strtotime($res[0]['DSG_RTN']));
	}

	public function getQAByHC($so, $customer) {
		
		$res = Database::rows(
			$this->db_ax,
			"SELECT DS.DSG_SUBHC
			FROM SALESTABLE S
			LEFT JOIN DSG_SALESTABLE DS ON DS.SALESID = S.SALESID
			LEFT JOIN CustTable CT ON CT.ACCOUNTNUM = S.CUSTACCOUNT
			WHERE S.SALESID = ?
			AND S.CUSTACCOUNT = ?
			AND S.QUOTATIONID is not null
			AND S.QUOTATIONID <> ''
			AND S.DATAAREAID = 'dsc'",
			[
				$so,
				$customer
			]
		);

		return $res[0]['DSG_SUBHC'];
	}

	public function getQAByBookingDetail($so, $customer) {
		
		$res = Database::rows(
			$this->db_ax,
			"SELECT DS.DSG_BOOKINGDETAIL
			FROM SALESTABLE S
			LEFT JOIN DSG_SALESTABLE DS ON DS.SALESID = S.SALESID
			LEFT JOIN CustTable CT ON CT.ACCOUNTNUM = S.CUSTACCOUNT
			WHERE S.SALESID = ?
			AND S.CUSTACCOUNT = ?
			AND S.QUOTATIONID is not null
			AND S.QUOTATIONID <> ''
			AND S.DATAAREAID = 'dsc'",
			[
				$so,
				$customer
			]
		);

		if (trim($res[0]['DSG_BOOKINGDETAIL']) === '' || 
				$res[0]['DSG_BOOKINGDETAIL'] === null) {
			return '-';
		}

		return $res[0]['DSG_BOOKINGDETAIL'];
	}

	public function getBookingSubject_internal($file,$customer,$type ) {
		
		$so = self::getSOFromFileBooking($file);
		$customer = self::getCustomerCode($file);
		
		if (count($so) === 0) {
			return '';
		}

		$txtPI = '';
		$txtNum = '';
		$txttoppicPi ='';
		$txttoppicnum ='';
		$tempTextPi = [];
		$tempTextxtNum = [];

		foreach ($so as $v) {
			if ( !in_array( $v, $tempTextPi ) ) {
				$txtPI = self::getQABySO($v, $customer);
				$txtNum = self::getQAByBOOKINGNUMBER($v, $customer);
				$tempTextPi[] = $txtPI;
				$tempTextxtNum[] = $txtNum;
			}
		}

		$counttempTextPi = array_count_values($tempTextPi);
		$counttempTextxtNum = array_count_values($tempTextxtNum);

		foreach ($counttempTextPi as $key => $value) {
			
			$txttoppicPi .= $key . ', ';
		}

		foreach ($counttempTextxtNum as $key => $value) {
			
			$txttoppicnum .= $key . ', ';
		}

		$text = '';

		if($type == 'New'){
			$text .= 'New Booking : '. self::getCustNameFromSO($so[0], $customer). ' / '
					 . trim($txttoppicPi, ', '). ' / ' 
					 . self::arrayToString($so) .' / ' 
					 . trim($txttoppicnum, ', ')  ;
		}
		else{
			$text .= 'Revised Booking : '. self::getCustNameFromSO($so[0], $customer). ' / '
					 . trim($txttoppicPi, ', '). ' / ' 
					 . self::arrayToString($so) .' / ' 
					 . trim($txttoppicnum, ', ')  ;
		}
						
		return $text;
	}

	public function getBookingBody_v4($file,$customer) {
		$text = '';

		$so = self::getSOFromFileBooking($file);
		$customer = self::getCustomerCode($file);
		
		if (count($so) === 0) {
			return '';
		}

		$txtPI = '';
		$txtPO = '';
		$HC = '';
		$txtAgent = '';
		$Booking_detail = '';
		$txttoppicPi = '';
		$txttoppicPo = '';
		$txttoppicHc = '';
		$txttoppicAgent = '';
		$tempTextPi = [];
		$dataTextPi = [];
		$dataTextPo = [];
		$dataTextHc = [];
		$dataTextAgent = [];

		foreach ($so as $v) {
			if ( !in_array( $v, $tempTextPi ) ) {
				$txtPI = self::getQABySO($v, $customer);
				$tempTextPi[] = $v;
				$dataTextPi[] = $txtPI;
			}
		}

		foreach ($so as $v2) {
			$txtPO = self::getCustomerPOFromSO($v2, $customer);
			$HC = self::getQAByHC($v2, $customer);
			$txtAgent = self::getAgent($v2, $customer);
			$dataTextPo[] = $txtPO ;
			$dataTextHc[] = $HC ;
			$dataTextAgent[] = $txtAgent ;		
		}  

		$counttempTextPi = array_count_values($dataTextPi);
		$counttempTextxtPo = array_count_values($dataTextPo);
		$counttempTextxtHc = array_count_values($dataTextHc);
		$counttempTextAgent = array_count_values($dataTextAgent);

		foreach ($counttempTextPi as $key => $value) {
			$txttoppicPi .= $key . ', ';
		}

		foreach ($counttempTextxtPo as $key => $value) {
			$txttoppicPo .= $key . ', ';
		}

		foreach ($counttempTextxtHc as $key => $value) {
			$txttoppicHc .= $key . ', ';
		}
		foreach ($counttempTextAgent as $key => $value) {
			$txttoppicAgent .= $key . ', ';
		}
    
		$text .= 'Dear EL, <br><br>';
		$text .= '<b>Customer name : </b>' . self::getCustNameFromSO($so[0], $customer) . '<br>';
		$text .= '<b>P/I : </b>' . trim($txttoppicPi, ', ') . '<br>';
		$text .= '<b>SO : </b>' . self::arrayToString($so) . '<br>';
		$text .= '<b>PO : </b>'. trim($txttoppicPo, ', ') .  '<br>';
		$text .= '<b>Loading date : </b>'. self::getEDDFromSO($so[0], $customer);
		$text .= '<b> CY : </b>'. self::getQAByCY($so[0], $customer);
		$text .= '<b> RTN : </b>'.self::getQAByRTN($so[0], $customer).' <br>';
		$text .= '<b> Agent : </b>'.trim($txttoppicAgent, ', ').' <br><br>';
		$text .= '<b>Sub\'HC : </b>'.trim($txttoppicHc, ', ').' <br>';
		$text .= '<b>Booking Detail : </b><br>';

		$text .= '<ul>';

		if(self::getQAByBookingDetail($so[0], $customer) !="") {
			$text .= '<li>'.self::getQAByBookingDetail($so[0], $customer).'</li>';
		}else{
			$text .= '-';
		}
		$text .= '</ul><br>';
		
		return  $text;
	}

	public function getEmail($projectId)
	{
		$sql = "SELECT 
		E.Email, 
		E.EmailType, 
		EmailCategory
		FROM EmailLists E
		WHERE ProjectID = $projectId
		AND [Status] = 1";

		$rows = Database::rows(
		$this->db_live,
		$sql
		);

		$to = [];
		$cc = [];
		// $sender = "";

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
			// else if ($row["EmailType"] === 4) 
			// {
			// 	$sender = $row["Email"];
			// }
		}

		return ["to" => $to, "cc" => $cc]; //, "sender" => $sender
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

	

}