<?php 

namespace App\Kpi;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;
use App\Common\CSRF;

class KpiAPI {

	public function __construct() {
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
		$this->automail = new Automail;
		$this->csrf = new CSRF;
    }
	
	public function getKpiSubject($type) {
		try {
			if ($type=='revised') {
				return 'Revised Confirm SO Approve online : '.date('d/m/Y');
			}else{
				return 'Confirm SO Approve online : '.date('d/m/Y');
			}
			
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getKpiBody($today,$status,$userid,$nonce,$listso) {
		try {
			$csrf = $this->csrf->generate();
            
			$url = 'http://lungryn.deestonegrp.com:8910/kpi/waiting?userid='.$userid.'&nonce='.$nonce; 

			$data = self::getSoConfirmBySO($today,$status,$userid,$listso);
			$txt = '';
			$txt .= '<label>SO Confirmation</label><br/>';
			$txt .= '<label>Status : Waiting</label><br/><br/>';

			$txt .= '<style>
					table, th, td { 
						border-collapse: collapse; 
						border: 1px solid #b2b2b2;
						font-family: "Cordia New";
						font-size: 22px;
					}
					label,a {
						font-family: "Cordia New";
						font-size: 22px;
					}
					</style>';

			$txt .= '<table>';
			$txt .= '<tr style="background-color: #d9edf6;">';
			$txt .= '<td valign="top"><label>CUSTNAME</label></td>';
			$txt .= '<td valign="top"><label>TOPORT</label></td>';
			$txt .= '<td valign="top"><label>QUOTATION ID</label></td>';
			$txt .= '<td valign="top"><label>SALES ID</label></td>';
			// $txt .= '<td valign="top"><label>SALESID FACTORY</label></td>';
			// $txt .= '<td valign="top"><label>FACTORY</label></td>';
			$txt .= '<td valign="top"><label>ORDER OF COMPANY</label></td>';
			$txt .= '<td valign="top"><label>CUSTOMER REF.</label></td>';
			$txt .= '<td valign="top"><label>TOTAL CU.M</label></td>';
			$txt .= '<td valign="top"><label>20\' FCL</label></td>';
			$txt .= '<td valign="top"><label>40\' FCL</label></td>';
			$txt .= '<td valign="top"><label>40\' HQ</label></td>';
			// $txt .= '<td valign="top"><label>REMARK (SO)</label></td>';
			// $txt .= '<td valign="top"><label>REMARK (PI)</label></td>';
			$txt .= '<td valign="top"><label>REQUEST SHIP DATE</label></td>';
			$txt .= '<td valign="top"><label>SALE NAME</label></td>';
			$txt .= '<td valign="top"><label>SO CONFIRM DATE</label></td>';
			$txt .= '<td valign="top"><label>DATA ENTRY</label></td>';
			$txt .= '<td valign="top"><label>REMARK REVISED</label></td>';
			$txt .= '</tr>';

			foreach ($data as $key => $value) {  
	 			$txt .= "<tr>";
	 			$txt .= "<td>".$value['CUSTNAME']."</td>";
	 			$txt .= "<td>".$value['TOPORT']."</td>";
	 			$txt .= "<td>".$value['QUOTATIONID']."</td>";
	 			$txt .= "<td>".$value['SALESID']."</td>";
	 			// $txt .= "<td>".$value['SALESIDFACT']."</td>";
	 			// $txt .= "<td>".$value['COMPANYID']."</td>";
	 			$txt .= "<td>".$value['DSG_REFCOMPANYID']."</td>";
	 			$txt .= "<td>".$value['CUSTOMERREF']."</td>";
	 			$txt .= "<td>".$value['CUMX']."</td>";
	 			$txt .= "<td>".number_format($value['CON20'])."</td>";
	 			$txt .= "<td>".number_format($value['CON40'])."</td>";
	 			$txt .= "<td>".number_format($value['CON40HQ'])."</td>";
	 			// $txt .= "<td>".nl2br($value['REMARKS'])."</td>";
	 			// $txt .= "<td>".nl2br($value['DOCUINTRO'])."</td>";
	 			$txt .= "<td>".$value['DSG_REQUESTSHIPDATE']."</td>";
	 			$txt .= "<td>".$value['SALESNAME']."</td>";
	 			$txt .= "<td>".date("d/m/Y", strtotime($value['SOCONDATE']))."</td>";
	 			$txt .= "<td>".$value['CONNAME']."</td>";
	 			$txt .= "<td>".$value['DSG_REASONREVISEORDERNAME']."</td>";
	 		// 	$txt .= "</tr>";

	 		// 	$txt .= '<tr>';
	 		// 	$txt .= '<td valign="top" style="background-color: #cfd9df;"><label>REMARK (SO)</label></td>';
				// $txt .= "<td colspan='15'>".nl2br($value['REMARKS'])."</td>";
				// $txt .= '</tr>';
				// $txt .= '<tr>';
				// $txt .= '<td valign="top" style="background-color: #cfd9df;"><label>REMARK (PI)</label></td>';
				// $txt .= "<td colspan='15'>".nl2br($value['DOCUINTRO'])."</td>";
				$txt .= '</tr>';
	 		} 

			$txt .= '</table>';

			$txt .= '<br><label>ยืนยันข้อมูลเพื่อทำการ</label> <a href='.$url.'>APPROVE</a>';

			return $txt;
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function mapSoConfirm($today,$status,$userid) {
		try {
			return Database::hasRows(
				$this->db_ax,
				"SELECT	
					CJ.MODIFIEDBY	
						
					FROM SALESTABLE ST
					LEFT JOIN SALESTABLELINKS LINK
						ON LINK.DATAAREAID = ST.DATAAREAID
						AND LINK.SUBSALESID = ST.SALESID
					LEFT JOIN SALESQUOTATIONTABLE QT
						ON QT.QUOTATIONID = ST.QUOTATIONID
						AND QT.DATAAREAID = ST.DATAAREAID
					LEFT JOIN (
								SELECT QL.QUOTATIONID,QL.DATAAREAID, SUM(QL.CUM)[CUM]
								FROM SALESQUOTATIONLINE QL
								GROUP BY QL.QUOTATIONID,QL.DATAAREAID
							   )QL
								ON QL.QUOTATIONID = QT.QUOTATIONID
								AND QL.DATAAREAID = QT.DATAAREAID
					LEFT JOIN CUSTCONFIRMJOUR CJ
						ON	CJ.SALESID		= ST.SALESID
						AND CJ.DATAAREAID	= ST.DATAAREAID
						AND CJ.CONFIRMDOCNUM = (
													SELECT CJ.SALESID + '-' + MAX(RIGHT(CJ.CONFIRMDOCNUM, 1)) [CONFIRMDOCNUM]
													FROM CUSTCONFIRMJOUR CJ
													WHERE CJ.SALESID	= ST.SALESID
													  AND CJ.DATAAREAID	= ST.DATAAREAID
													GROUP BY CJ.SALESID, CJ.DATAAREAID
												)
					LEFT JOIN USERINFO USERCON
						ON USERCON.ID	= CJ.CREATEDBY
					LEFT JOIN EMPLTABLE SALES
						ON SALES.EMPLID = ST.DSG_SALESREF AND Sales.DATAAREAID = CASE WHEN ST.DATAAREAID = 'DSC' THEN 'DSC' ELSE 'DV'END
					LEFT JOIN DSG_REFERENCEDSC REF
					ON	REF.SALESIDDSC	= CASE WHEN ST.DATAAREAID = 'DSC' THEN ST.SALESID ELSE '' END
					AND REF.DATAAREAID	= 'DC'
					LEFT JOIN SALESQUOTATIONTABLE SQ
					ON SQ.DATAAREAID = ST.DATAAREAID
					AND SQ.QUOTATIONID = ST.QUOTATIONID
					WHERE CJ.CONFIRMDATE = ?
					AND CJ.DSG_CONFIRMSOSTATUS = ? 
					-- WHERE ST.SALESID IN ('SO19-036710','SO19-036717','SO19-036623')
					AND CJ.MODIFIEDBY = ?
					-- AND CJ.CREATEDBY = ?
					ORDER BY CJ.MODIFIEDBY ASC ",[$today,$status,$userid]
			);
		} catch (\Exception $th) {
			return $e->getMessage();
		}
	}

	public function getSo($today,$status,$userid) {
		try {
			
			$query = Database::rows(
				$this->db_ax,
				"SELECT	
					CJ.SALESID,
					CJ.ConfirmDocNum,
					CJ.MODIFIEDBY,	
					(SELECT TOP 1 DSG_CONFIRMSOSTATUS FROM CUSTCONFIRMJOUR WHERE SALESID = CJ.SALESID AND DSG_CONFIRMSOSTATUS >= 2) AS 'Revised'	
					FROM SALESTABLE ST
					LEFT JOIN SALESTABLELINKS LINK
						ON LINK.DATAAREAID = ST.DATAAREAID
						AND LINK.SUBSALESID = ST.SALESID
					LEFT JOIN SALESQUOTATIONTABLE QT
						ON QT.QUOTATIONID = ST.QUOTATIONID
						AND QT.DATAAREAID = ST.DATAAREAID
					LEFT JOIN (
								SELECT QL.QUOTATIONID,QL.DATAAREAID, SUM(QL.CUM)[CUM]
								FROM SALESQUOTATIONLINE QL
								GROUP BY QL.QUOTATIONID,QL.DATAAREAID
							   )QL
								ON QL.QUOTATIONID = QT.QUOTATIONID
								AND QL.DATAAREAID = QT.DATAAREAID
					LEFT JOIN CUSTCONFIRMJOUR CJ
						ON	CJ.SALESID		= ST.SALESID
						AND CJ.DATAAREAID	= ST.DATAAREAID
						AND CJ.CONFIRMDOCNUM = (
													SELECT CJ.SALESID + '-' + MAX(RIGHT(CJ.CONFIRMDOCNUM, 1)) [CONFIRMDOCNUM]
													FROM CUSTCONFIRMJOUR CJ
													WHERE CJ.SALESID	= ST.SALESID
													  AND CJ.DATAAREAID	= ST.DATAAREAID
													  AND CJ.DSG_CONFIRMSOSTATUS <> 0
													GROUP BY CJ.SALESID, CJ.DATAAREAID
												)
					LEFT JOIN USERINFO USERCON
						ON USERCON.ID	= CJ.CREATEDBY
					LEFT JOIN EMPLTABLE SALES
						ON SALES.EMPLID = ST.DSG_SALESREF AND Sales.DATAAREAID = CASE WHEN ST.DATAAREAID = 'DSC' THEN 'DSC' ELSE 'DV'END
					LEFT JOIN DSG_REFERENCEDSC REF
					ON	REF.SALESIDDSC	= CASE WHEN ST.DATAAREAID = 'DSC' THEN ST.SALESID ELSE '' END
					AND REF.DATAAREAID	= 'DC'
					LEFT JOIN SALESQUOTATIONTABLE SQ
					ON SQ.DATAAREAID = ST.DATAAREAID
					AND SQ.QUOTATIONID = ST.QUOTATIONID
					WHERE CJ.CONFIRMDATE = ?
					AND CJ.DSG_CONFIRMSOSTATUS = ? 
					AND CJ.MODIFIEDBY = ?
					AND CJ.CUSTGROUP = ?
					ORDER BY CJ.MODIFIEDBY ASC ",[$today,1,$userid,'OVS']
			);

			$listsorevised=[];
			$listsowaiting=[];
			$listsoall_revised=[];
			$listsoall_waiting=[];
			foreach ($query as $key => $value) {
				if ($value['Revised']>=2) {
					array_push($listsorevised, $value['SALESID']);
					array_push($listsoall_revised, $value['ConfirmDocNum']);
				}else{
					array_push($listsowaiting, $value['SALESID']);
					array_push($listsoall_waiting, $value['ConfirmDocNum']);
				}
				// array_push($listsoall, $value['ConfirmDocNum']);
			}
			if ($status==1) {
				return $listsowaiting;
			}else if($status==2){
				return $listsorevised;
			}else if($status==3) {
				return $listsoall_waiting;
			}else if($status==4){
				return $listsoall_revised;
			}
			
		} catch (\Exception $th) {
			return $e->getMessage();
		}
	}

	public function getSoConfirm($today,$status,$userid) {
		try {
			return Database::rows(
				$this->db_ax,
				"SELECT	
					CASE WHEN USERCON.NAME IS NULL THEN '' ELSE USERCON.NAME END [CONNAME]
					,ST.DATAAREAID,
					ST.SALESNAME [CUSTNAME],
					ST.DSG_TOPORTDESC [TOPORT],
					ST.QUOTATIONID,
					ST.SALESID,
					ST.CUSTOMERREF,
					ST.DSG_CONTAINER1X20 [CON20],
					ST.DSG_CONTAINER1X40 [CON40],
					ST.DSG_CONTAINER1X40HC [CON40HQ],
					ST.DSG_CONTAINER1X45HC [CON45HQ],
					SALES.NAME + ' ' + SALES.ALIAS [SALESNAME],
					ST.CREATEDDATE [PICONDATE],
					CONVERT(CHAR(8), DATEADD(SECOND, ST.CREATEDTIME, ''), 114) [PICONTIME],
					CJ.CREATEDDATE [SOCONDATE],
					CONVERT(CHAR(8), DATEADD(SECOND, CJ.CREATEDTIME, ''), 114) [SOCONTIME],
					CASE WHEN ST.DOCUMENTSTATUS = 0 THEN 1 ELSE 0 END [WIP_ECS],
					
					--- EDIT FOR 2019-719
					CASE WHEN ST.CREATEDDATE = CJ.CREATEDDATE -- กรณี วันที่ Create SO และวันที่ Confirm SO เป็นเดียวกัน
							THEN 
								CASE	WHEN ST.CREATEDTIME > 28800 AND CJ.CREATEDTIME < 61200 -- start และ end ในช่วง 8 - 17
								THEN 
									CASE	WHEN  ST.CREATEDTIME BETWEEN 43200 AND 46800  -- ทั้งสองอันอยู่ในช่วงเที่ยง
												AND CJ.CREATEDTIME BETWEEN 43200 AND 46800 
											THEN 0
											WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800  -- start ในช่วงเที่ยง end เกินเที่ยง
												AND CJ.CREATEDTIME NOT BETWEEN 43200 AND 46800 
											THEN  CJ.CREATEDTIME-46800 
											WHEN ST.CREATEDTIME NOT BETWEEN 43200 AND 46800  -- start ก่อนเที่ยง  end ในช่วงเที่ยง
												AND CJ.CREATEDTIME BETWEEN 43200 AND 46800
											THEN 43200-ST.CREATEDTIME 
											WHEN ST.CREATEDTIME < 43200  -- start ก่อนเที่ยง  end หลังเที่ยง
												AND CJ.CREATEDTIME > 46800  
											THEN (CJ.CREATEDTIME-ST.CREATEDTIME)-3600
									ELSE CJ.CREATEDTIME-ST.CREATEDTIME END
												
								WHEN ST.CREATEDTIME < 28800 AND CJ.CREATEDTIME BETWEEN 28800 AND 61200 -- START ก่อน 8 end ก่อน 17
								THEN 
								
									CASE	WHEN  CJ.CREATEDTIME < 43200  -- END ก่อนเที่ยง
											THEN  CJ.CREATEDTIME-28800 
											WHEN CJ.CREATEDTIME BETWEEN 43200 AND 46800 -- END ช่วงเที่ยง
											THEN 43200-28800 
											WHEN CJ.CREATEDTIME > 46800  -- END หลังเที่ยง
											THEN (CJ.CREATEDTIME-28800)-3600
									END
								WHEN ST.CREATEDTIME BETWEEN 28800 AND 61200 AND CJ.CREATEDTIME > 61200 -- START หลัง 8 end หลัง 17
								THEN 
								
									CASE	WHEN  ST.CREATEDTIME < 43200  -- START ก่อนเที่ยง
											THEN  (61200-ST.CREATEDTIME)-3600 
											WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800 -- START ช่วงเที่ยง
											THEN 61200-46800 
											WHEN CJ.CREATEDTIME > 46800  -- START หลังเที่ยง
											THEN 61200-ST.CREATEDTIME
									END
								WHEN ST.CREATEDTIME < 28800 AND CJ.CREATEDTIME > 61200 -- START ก่อน 8 end หลัง 17
								THEN 
											(61200-28800)-3600 
								ELSE 0
								END	
						WHEN ST.CREATEDDATE = CJ.CREATEDDATE AND DATEPART(DW, ST.CREATEDDATE)= 1 -- วันเดียวกัน แต่เป็นวันอาทิตย์
							THEN 0
							
						WHEN ST.CREATEDDATE != CJ.CREATEDDATE -- กรณีวันที่ Create SO และวันที่ Confirm SO ไม่ใช่วันที่เดียวกัน
							THEN 
								CASE WHEN ST.CREATEDTIME < 28800  -- START < 8  
							THEN 28800 	
							
							WHEN ST.CREATEDTIME BETWEEN 28800 AND 43200 -- START 8 - 12
							THEN 61200 - 3600 - ST.CREATEDTIME
							
							WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800 -- START 12 - 13
							THEN 14400
							
							WHEN ST.CREATEDTIME BETWEEN 46800 AND 61200 -- START 13 - 17
							THEN 61200 - ST.CREATEDTIME
							
							WHEN ST.CREATEDTIME > 61200 -- START > 17
							THEN 0
							
							END
								+ 
								CASE WHEN CJ.CREATEDTIME < 28800 -- END < 8
								THEN 0
								
								WHEN CJ.CREATEDTIME BETWEEN 28800 AND 43200 --END 8 - 12
								THEN CJ.CREATEDTIME-28800
								
								WHEN CJ.CREATEDTIME BETWEEN 43200 AND 46800 -- END 12-13
								THEN 14400
								
								WHEN CJ.CREATEDTIME BETWEEN 46800 AND 61200 -- END 13-17
								THEN 61200 - CJ.CREATEDTIME
								
								WHEN CJ.CREATEDTIME > 61200 -- END > 17
								THEN 28800
								END
									
							+((28800*(DATEDIFF(DAY,ST.CREATEDDATE,CJ.CREATEDDATE)-1))-(28800 * DateDiff(ww, ST.CREATEDDATE, CJ.CREATEDDATE))) -- วันที่เหลือ - วันอาทิตย์
											
						
					ELSE 0 END [H]
					
					
					,CASE WHEN ISNULL(LINK.MAINSALESID, '') = '' THEN 0
						 ELSE 1
					END [CHILD]
					,ST.DSG_AVAILABLEDATE
					--,CASE WHEN @SECTION = 'A' THEN SQ.DSG_EDDDATE ELSE ST.DSG_EDDDATE END[EDDDATE]
					,ST.DSG_PRIMARYAGENTID
					,ST.DSG_LOADINGPLANT
					,ST.REMARKS
					,ST.DSG_REQUESTSHIPDATE
					,REF.SALESIDFACT
					,REF.COMPANYID
					,ST.DSG_REFCOMPANYID
					,CASE WHEN SQ.DSG_BOOKINGSTATUS = 1 THEN 'NotBooked'
						  WHEN SQ.DSG_BOOKINGSTATUS = 2 THEN 'Booked'
						  WHEN SQ.DSG_BOOKINGSTATUS = 3 THEN 'Confirmed'
						  WHEN SQ.DSG_BOOKINGSTATUS = 4 THEN 'Incorrect'
					ELSE '' END  [BOOKINGSTATUS]
					,CONVERT(varchar, DATEADD(ss, ST.CREATEDTIME, 0), 108) --START TIME
					,CONVERT(varchar, DATEADD(ss, CJ.CREATEDTIME, 0), 108) -- END TIME
					,CONVERT(varchar, DATEADD(ss, CJ.CREATEDTIME-ST.CREATEDTIME, 0), 108) -- NORMAL CAL
					,CONVERT(varchar, DATEADD(ss,  -- REAL CAL
					
					CASE WHEN ST.CREATEDDATE = CJ.CREATEDDATE AND DATEPART(DW, ST.CREATEDDATE)!= 1-- กรณี วันที่ Create SO และวันที่ Confirm SO เป็นเดียวกัน และไม่ใช่วันอาทิตย์
							THEN 
								CASE	WHEN ST.CREATEDTIME > 28800 AND CJ.CREATEDTIME < 61200 -- start และ end ในช่วง 8 - 17
								THEN 
								
									CASE	WHEN  ST.CREATEDTIME BETWEEN 43200 AND 46800  -- ทั้งสองอันอยู่ในช่วงเที่ยง
												AND CJ.CREATEDTIME BETWEEN 43200 AND 46800 
												THEN 0
											WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800  -- start ในช่วงเที่ยง end เกินเที่ยง
												AND CJ.CREATEDTIME NOT BETWEEN 43200 AND 46800 
											THEN  CJ.CREATEDTIME-46800 
											WHEN ST.CREATEDTIME NOT BETWEEN 43200 AND 46800  -- start ก่อนเที่ยง  end ในช่วงเที่ยง
												AND CJ.CREATEDTIME BETWEEN 43200 AND 46800
											THEN 43200-ST.CREATEDTIME 
											WHEN ST.CREATEDTIME < 43200  -- start ก่อนเที่ยง  end หลังเที่ยง
												AND CJ.CREATEDTIME > 46800  
											THEN (CJ.CREATEDTIME-ST.CREATEDTIME)-3600
									ELSE CJ.CREATEDTIME-ST.CREATEDTIME END
									
								WHEN ST.CREATEDTIME < 28800 AND CJ.CREATEDTIME BETWEEN 28800 AND 61200 -- START ก่อน 8 end ก่อน 17
								THEN 
								
									CASE	WHEN  CJ.CREATEDTIME < 43200  -- END ก่อนเที่ยง
											THEN  CJ.CREATEDTIME-28800 
											WHEN CJ.CREATEDTIME BETWEEN 43200 AND 46800 -- END ช่วงเที่ยง
											THEN 43200-28800 
											WHEN CJ.CREATEDTIME > 46800  -- END หลังเที่ยง
											THEN (CJ.CREATEDTIME-28800)-3600
									END
								WHEN ST.CREATEDTIME BETWEEN 28800 AND 61200 AND CJ.CREATEDTIME > 61200 -- START หลัง 8 end หลัง 17
								THEN 
								
									CASE	WHEN  ST.CREATEDTIME < 43200  -- START ก่อนเที่ยง
											THEN  (61200-ST.CREATEDTIME)-3600 
											WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800 -- START ช่วงเที่ยง
											THEN 61200-46800 
											WHEN CJ.CREATEDTIME > 46800  -- START หลังเที่ยง
											THEN 61200-ST.CREATEDTIME
									END
								WHEN ST.CREATEDTIME < 28800 AND CJ.CREATEDTIME > 61200 -- START ก่อน 8 end หลัง 17
								THEN 
											(61200-28800)-3600 
								ELSE 0
								END	
						WHEN ST.CREATEDDATE = CJ.CREATEDDATE AND DATEPART(DW, ST.CREATEDDATE)= 1 -- วันเดียวกัน แต่เป็นวันอาทิตย์
							THEN 0
						WHEN ST.CREATEDDATE != CJ.CREATEDDATE -- กรณีวันที่ Create SO และวันที่ Confirm SO ไม่ใช่วันที่เดียวกัน
							THEN 
								CASE WHEN ST.CREATEDTIME < 28800  -- START < 8  
								THEN 28800 	
								
								WHEN ST.CREATEDTIME BETWEEN 28800 AND 43200 -- START 8 - 12
								THEN 61200 - 3600 - ST.CREATEDTIME
								
								WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800 -- START 12 - 13
								THEN 14400
								
								WHEN ST.CREATEDTIME BETWEEN 46800 AND 61200 -- START 13 - 17
								THEN 61200 - ST.CREATEDTIME
								
								WHEN ST.CREATEDTIME > 61200 -- START > 17
								THEN 0
								
								END
									+ 
									CASE WHEN CJ.CREATEDTIME < 28800 -- END < 8
									THEN 0
									
									WHEN CJ.CREATEDTIME BETWEEN 28800 AND 43200 --END 8 - 12
									THEN CJ.CREATEDTIME-28800
									
									WHEN CJ.CREATEDTIME BETWEEN 43200 AND 46800 -- END 12-13
									THEN 14400
									
									WHEN CJ.CREATEDTIME BETWEEN 46800 AND 61200 -- END 13-17
									THEN 61200 - CJ.CREATEDTIME
									
									WHEN CJ.CREATEDTIME > 61200 -- END > 17
									THEN 28800
									END
										
								+((28800*(DATEDIFF(DAY,ST.CREATEDDATE,CJ.CREATEDDATE)-1))-(28800 * DateDiff(ww, ST.CREATEDDATE, CJ.CREATEDDATE))) -- วันที่เหลือ - วันอาทิตย์
								
						
					ELSE 0 END 
					, 0), 108)
					,QL.CUM 
					,CONVERT(NVARCHAR(1800),QT.DOCUINTRO) AS DOCUINTRO
					,CONVERT(NVARCHAR(1800),QT.DOCUCONCLUSION)--Nut CPM2019-1418 20190820
					,CJ.DSG_CONFIRMSOSTATUS
					,CASE 
						WHEN CJ.DSG_CONFIRMSOSTATUS = 1 THEN 'Waiting'
						WHEN CJ.DSG_CONFIRMSOSTATUS = 2 THEN 'Approved'
						WHEN CJ.DSG_CONFIRMSOSTATUS = 3 THEN 'Reject'
					ELSE 'NO STAUTS' END AS CONFIRMSOSTATUS
					,CJ.MODIFIEDBY	
					,(SELECT TOP 1 DSG_CONFIRMSOSTATUS FROM CUSTCONFIRMJOUR WHERE SALESID = CJ.SALESID AND DSG_CONFIRMSOSTATUS = 2) AS 'Revised'
					,SP.DSG_REASONREVISEORDERID
					,RVO.DSG_REASONREVISEORDERNAME
					,CJ.CONFIRMID
					,CUMX.CUM[CUMX] 

					FROM SALESTABLE ST
					LEFT JOIN SALESTABLELINKS LINK
						ON LINK.DATAAREAID = ST.DATAAREAID
						AND LINK.SUBSALESID = ST.SALESID
					LEFT JOIN SALESQUOTATIONTABLE QT
						ON QT.QUOTATIONID = ST.QUOTATIONID
						AND QT.DATAAREAID = ST.DATAAREAID
					LEFT JOIN (
								SELECT QL.QUOTATIONID,QL.DATAAREAID, SUM(QL.CUM)[CUM]
								FROM SALESQUOTATIONLINE QL
								GROUP BY QL.QUOTATIONID,QL.DATAAREAID
							   )QL
								ON QL.QUOTATIONID = QT.QUOTATIONID
								AND QL.DATAAREAID = QT.DATAAREAID
					LEFT JOIN CUSTCONFIRMJOUR CJ
						ON	CJ.SALESID		= ST.SALESID
						AND CJ.DATAAREAID	= ST.DATAAREAID
						AND CJ.CONFIRMDOCNUM = (
													SELECT CJ.SALESID + '-' + MAX(RIGHT(CJ.CONFIRMDOCNUM, 1)) [CONFIRMDOCNUM]
													FROM CUSTCONFIRMJOUR CJ
													WHERE CJ.SALESID	= ST.SALESID
													  AND CJ.DATAAREAID	= ST.DATAAREAID
													  AND CJ.DSG_CONFIRMSOSTATUS <> 0
													GROUP BY CJ.SALESID, CJ.DATAAREAID
												)
					LEFT JOIN USERINFO USERCON
						ON USERCON.ID	= CJ.CREATEDBY
					LEFT JOIN EMPLTABLE SALES
						ON SALES.EMPLID = ST.DSG_SALESREF AND Sales.DATAAREAID = CASE WHEN ST.DATAAREAID = 'DSC' THEN 'DSC' ELSE 'DV'END
					LEFT JOIN DSG_REFERENCEDSC REF
					ON	REF.SALESIDDSC	= CASE WHEN ST.DATAAREAID = 'DSC' THEN ST.SALESID ELSE '' END
					AND REF.DATAAREAID	= 'DC'
					LEFT JOIN SALESQUOTATIONTABLE SQ
					ON SQ.DATAAREAID = ST.DATAAREAID
					AND SQ.QUOTATIONID = ST.QUOTATIONID

					LEFT JOIN SALESPARMTABLE SP
					ON SP.SALESID = ST.SALESID
					AND SP.CREATEDDATE = CJ.CREATEDDATE
					AND SP.PARMID = CJ.PARMID
				    LEFT JOIN DSG_REASONREVISEORDER RVO 
						ON RVO.DSG_REASONREVISEORDERID = SP.DSG_REASONREVISEORDERID    

					LEFT JOIN 
					(
						SELECT 
						CJCUM.SALESID
						,CJCUM.CONFIRMID
						,SUM
						(
						CASE WHEN STCUM.CON = '1X20' THEN (CASE WHEN I.DSG_CUMLOAD20 != 0 THEN (CR.QTY * (30 / I.DSG_CUMLOAD20)) ELSE 0 END)
						WHEN STCUM.CON = '1x40' THEN (CASE WHEN I.DSG_CUMLOAD40 != 0 THEN (CR.QTY * (60 / I.DSG_CUMLOAD40)) ELSE 0 END)
						WHEN STCUM.CON = '1x40HC' THEN (CASE WHEN I.DSG_CUMLOAD40HC != 0 THEN (CR.QTY * (70 / I.DSG_CUMLOAD40HC)) ELSE 0 END)
						WHEN STCUM.CON = '1x45HC' THEN (CASE WHEN I.DSG_CUMLOAD45HC != 0 THEN (CR.QTY * (33 / I.DSG_CUMLOAD45HC)) ELSE 0 END)
						ELSE 0 END
						) [CUM]
						FROM CUSTCONFIRMJOUR CJCUM
						JOIN CUSTCONFIRMTRANS CR
						ON CR.SALESID		= CJCUM.SALESID
						AND CR.CONFIRMID	= CJCUM.CONFIRMID
						AND CR.CONFIRMDATE	= CJCUM.CONFIRMDATE
						AND CR.DATAAREAID	= CJCUM.DATAAREAID
						LEFT JOIN 
						(
							SELECT
								STCUM.SALESID
								,STCUM.DATAAREAID
								,CASE WHEN ((STCUM.[1X45HC] > STCUM.[1X40HC]) AND (STCUM.[1X45HC] > STCUM.[1X40]) AND (STCUM.[1X45HC] > STCUM.[1X20]))
										 THEN '1x45HC'
										 WHEN ((STCUM.[1X40HC] > STCUM.[1X45HC]) AND (STCUM.[1X40HC] > STCUM.[1X40]) AND (STCUM.[1X40HC] > STCUM.[1X20]))
										 THEN '1x40HC'
										 WHEN ((STCUM.[1X40] > STCUM.[1X45HC]) AND (STCUM.[1X40] > STCUM.[1X40HC]) AND (STCUM.[1X40] > STCUM.[1X20]))
										 THEN '1x40'
										 WHEN ((STCUM.[1X20] > STCUM.[1X45HC]) AND (STCUM.[1X20] >STCUM.[1X40HC]) AND (STCUM.[1X20] > STCUM.[1X40]))
										 THEN '1x20'
										 ELSE
										 (
											CASE WHEN STCUM.[1X45HC] != 0
												 THEN '1x45HC'
												 WHEN STCUM.[1X40HC] != 0
												 THEN '1x40HC'
												 WHEN STCUM.[1X40] != 0
												 THEN '1x40'
												 ELSE '1x20' END
										 ) END [CON]
								FROM
								(
									SELECT 
									STCUM.SALESID
									,STCUM.DATAAREAID
									,CASE WHEN STCUM.DSG_CONTAINER1X20 != 0 OR STCUM.DSG_CONTAINER1X40 != 0 OR STCUM.DSG_CONTAINER1X40HC != 0 OR STCUM.DSG_CONTAINER1X45HC != 0
									  THEN ISNULL(STCUM.DSG_CONTAINER1X20,0) / 2
									  ELSE
									  (
										CASE WHEN STRF.SALESID IS NOT NULL THEN ISNULL(STRF.DSG_CONTAINER1X20,0) / 2
											 ELSE (ISNULL(STC.DSG_CONTAINER1X20,0) + ISNULL(STRC.DSG_CONTAINER1X20,0) + ISNULL(STB.DSG_CONTAINER1X20,0)) / 2
											 END
									  )	  
									END [1X20]
									,CASE WHEN STCUM.DSG_CONTAINER1X20 != 0 OR STCUM.DSG_CONTAINER1X40 != 0 OR STCUM.DSG_CONTAINER1X40HC != 0 OR STCUM.DSG_CONTAINER1X45HC != 0
									  THEN ISNULL(STCUM.DSG_CONTAINER1X40,0)
									  ELSE
									  (
										CASE WHEN STRF.SALESID IS NOT NULL THEN ISNULL(STRF.DSG_CONTAINER1X40,0)
											 ELSE ISNULL(STC.DSG_CONTAINER1X40,0) + ISNULL(STRC.DSG_CONTAINER1X40,0) + ISNULL(STB.DSG_CONTAINER1X40,0)
											 END
									  )	  
									END [1X40]
									,CASE WHEN STCUM.DSG_CONTAINER1X20 != 0 OR STCUM.DSG_CONTAINER1X40 != 0 OR STCUM.DSG_CONTAINER1X40HC != 0 OR STCUM.DSG_CONTAINER1X45HC != 0
									  THEN ISNULL(STCUM.DSG_CONTAINER1X40HC,0)
									  ELSE
									  (
										CASE WHEN STRF.SALESID IS NOT NULL THEN ISNULL(STRF.DSG_CONTAINER1X40HC,0)
											 ELSE ISNULL(STC.DSG_CONTAINER1X40HC,0) + ISNULL(STRC.DSG_CONTAINER1X40HC,0) + ISNULL(STB.DSG_CONTAINER1X40HC,0)
											 END
									  )	  
									END [1X40HC]
									,CASE WHEN STCUM.DSG_CONTAINER1X20 != 0 OR STCUM.DSG_CONTAINER1X40 != 0 OR STCUM.DSG_CONTAINER1X40HC != 0 OR STCUM.DSG_CONTAINER1X45HC != 0
									  THEN ISNULL(STCUM.DSG_CONTAINER1X45HC,0)
									  ELSE
									  (
										CASE WHEN STRF.SALESID IS NOT NULL THEN ISNULL(STRF.DSG_CONTAINER1X45HC,0)
											 ELSE ISNULL(STC.DSG_CONTAINER1X45HC,0) + ISNULL(STRC.DSG_CONTAINER1X45HC,0) + ISNULL(STB.DSG_CONTAINER1X45HC,0)
											 END
									  )	  
									END [1X45HC]
									FROM SALESTABLE STCUM
									
									LEFT JOIN CUSTCONFIRMSALESLINK CK
									ON CK.ORIGSALESID		= STCUM.SALESID
									AND CK.SALESID			!= STCUM.SALESID
									AND CK.DATAAREAID		= STCUM.DATAAREAID

									LEFT JOIN DSG_SALESBOOKINGREF SF
									ON SF.DSG_SALESIDREF	= CK.SALESID
									AND SF.DATAAREAID		= CK.DATAAREAID

									LEFT JOIN SALESTABLE STC
									ON STC.SALESID			= CK.SALESID
									AND STC.DATAAREAID		= CK.DATAAREAID

									LEFT JOIN SALESTABLE STRC
									ON STRC.SALESID			= SF.DSG_SALESID
									AND STRC.DATAAREAID		= SF.DATAAREAID
									
									LEFT JOIN SALESTABLELINKS BK
									ON BK.SUBSALESID		= STCUM.SALESID
									AND BK.DATAAREAID		= STCUM.DATAAREAID

									LEFT JOIN SALESTABLE STB
									ON STB.SALESID			= BK.MAINSALESID
									AND STB.DATAAREAID		= BK.DATAAREAID
									LEFT JOIN 
									(
										SELECT SRF.DSG_SALESIDREF,SRF.DATAAREAID,STRF.SALESID
										,STRF.DSG_CONTAINER1X20
										,STRF.DSG_CONTAINER1X40
										,STRF.DSG_CONTAINER1X40HC
										,STRF.DSG_CONTAINER1X45HC
										,ROW_NUMBER() 
											OVER(
												PARTITION BY SRF.DSG_SALESIDREF,SRF.DATAAREAID
												ORDER BY STRF.SALESID DESC
												)AS NUM
										FROM DSG_SALESBOOKINGREF SRF
										LEFT JOIN SALESTABLE STRF
										ON STRF.SALESID			= SRF.DSG_SALESID
										AND STRF.DATAAREAID		= SRF.DATAAREAID
									)STRF
									ON STRF.DSG_SALESIDREF	= STCUM.SALESID
									AND STRF.DATAAREAID		= STCUM.DATAAREAID
									AND STRF.NUM			= 1
								)STCUM

							)STCUM
							ON STCUM.SALESID		= CR.SALESID
							AND STCUM.DATAAREAID	= CR.DATAAREAID
							JOIN INVENTTABLE I
							ON I.ITEMID			= CR.ITEMID
							AND I.DATAAREAID	= 'DSC'

						WHERE CJCUM.DATAAREAID	= 'DSC'
						--AND CJCUM.SALESID = ST.SALESID
						GROUP BY 
						CJCUM.SALESID
						,CJCUM.CONFIRMID
					)CUMX ON CJ.SALESID = CUMX.SALESID  
						AND CJ.CONFIRMID = CUMX.CONFIRMID

					WHERE 
					-- CJ.CONFIRMDATE = ?
					CJ.DSG_CONFIRMSOSTATUS = ? 
					-- WHERE ST.SALESID IN ('SO19-036710','SO19-036717','SO19-036623')
					AND CJ.MODIFIEDBY = ?
					-- AND CJ.CREATEDBY = ?
					ORDER BY CJ.MODIFIEDBY,ST.SALESID ASC ",[$status,$userid]
			);
		} catch (\Exception $th) {
			return $e->getMessage();
		}
	}

	public function getSoConfirmBySO($today,$status,$userid,$listso) {
		try {

			$so  = '';
            foreach ($listso as $s) {
                $so .= "'".$s."'" . ', ';
            }
            $so = trim($so, ', ');

			$sql = "SELECT	
					CASE WHEN USERCON.NAME IS NULL THEN '' ELSE USERCON.NAME END [CONNAME]
					,ST.DATAAREAID,
					ST.SALESNAME [CUSTNAME],
					ST.DSG_TOPORTDESC [TOPORT],
					ST.QUOTATIONID,
					ST.SALESID,
					ST.CUSTOMERREF,
					ST.DSG_CONTAINER1X20 [CON20],
					ST.DSG_CONTAINER1X40 [CON40],
					ST.DSG_CONTAINER1X40HC [CON40HQ],
					ST.DSG_CONTAINER1X45HC [CON45HQ],
					SALES.NAME + ' ' + SALES.ALIAS [SALESNAME],
					ST.CREATEDDATE [PICONDATE],
					CONVERT(CHAR(8), DATEADD(SECOND, ST.CREATEDTIME, ''), 114) [PICONTIME],
					CJ.CREATEDDATE [SOCONDATE],
					CONVERT(CHAR(8), DATEADD(SECOND, CJ.CREATEDTIME, ''), 114) [SOCONTIME],
					CASE WHEN ST.DOCUMENTSTATUS = 0 THEN 1 ELSE 0 END [WIP_ECS],
					
					--- EDIT FOR 2019-719
					CASE WHEN ST.CREATEDDATE = CJ.CREATEDDATE -- กรณี วันที่ Create SO และวันที่ Confirm SO เป็นเดียวกัน
							THEN 
								CASE	WHEN ST.CREATEDTIME > 28800 AND CJ.CREATEDTIME < 61200 -- start และ end ในช่วง 8 - 17
								THEN 
									CASE	WHEN  ST.CREATEDTIME BETWEEN 43200 AND 46800  -- ทั้งสองอันอยู่ในช่วงเที่ยง
												AND CJ.CREATEDTIME BETWEEN 43200 AND 46800 
											THEN 0
											WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800  -- start ในช่วงเที่ยง end เกินเที่ยง
												AND CJ.CREATEDTIME NOT BETWEEN 43200 AND 46800 
											THEN  CJ.CREATEDTIME-46800 
											WHEN ST.CREATEDTIME NOT BETWEEN 43200 AND 46800  -- start ก่อนเที่ยง  end ในช่วงเที่ยง
												AND CJ.CREATEDTIME BETWEEN 43200 AND 46800
											THEN 43200-ST.CREATEDTIME 
											WHEN ST.CREATEDTIME < 43200  -- start ก่อนเที่ยง  end หลังเที่ยง
												AND CJ.CREATEDTIME > 46800  
											THEN (CJ.CREATEDTIME-ST.CREATEDTIME)-3600
									ELSE CJ.CREATEDTIME-ST.CREATEDTIME END
												
								WHEN ST.CREATEDTIME < 28800 AND CJ.CREATEDTIME BETWEEN 28800 AND 61200 -- START ก่อน 8 end ก่อน 17
								THEN 
								
									CASE	WHEN  CJ.CREATEDTIME < 43200  -- END ก่อนเที่ยง
											THEN  CJ.CREATEDTIME-28800 
											WHEN CJ.CREATEDTIME BETWEEN 43200 AND 46800 -- END ช่วงเที่ยง
											THEN 43200-28800 
											WHEN CJ.CREATEDTIME > 46800  -- END หลังเที่ยง
											THEN (CJ.CREATEDTIME-28800)-3600
									END
								WHEN ST.CREATEDTIME BETWEEN 28800 AND 61200 AND CJ.CREATEDTIME > 61200 -- START หลัง 8 end หลัง 17
								THEN 
								
									CASE	WHEN  ST.CREATEDTIME < 43200  -- START ก่อนเที่ยง
											THEN  (61200-ST.CREATEDTIME)-3600 
											WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800 -- START ช่วงเที่ยง
											THEN 61200-46800 
											WHEN CJ.CREATEDTIME > 46800  -- START หลังเที่ยง
											THEN 61200-ST.CREATEDTIME
									END
								WHEN ST.CREATEDTIME < 28800 AND CJ.CREATEDTIME > 61200 -- START ก่อน 8 end หลัง 17
								THEN 
											(61200-28800)-3600 
								ELSE 0
								END	
						WHEN ST.CREATEDDATE = CJ.CREATEDDATE AND DATEPART(DW, ST.CREATEDDATE)= 1 -- วันเดียวกัน แต่เป็นวันอาทิตย์
							THEN 0
							
						WHEN ST.CREATEDDATE != CJ.CREATEDDATE -- กรณีวันที่ Create SO และวันที่ Confirm SO ไม่ใช่วันที่เดียวกัน
							THEN 
								CASE WHEN ST.CREATEDTIME < 28800  -- START < 8  
							THEN 28800 	
							
							WHEN ST.CREATEDTIME BETWEEN 28800 AND 43200 -- START 8 - 12
							THEN 61200 - 3600 - ST.CREATEDTIME
							
							WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800 -- START 12 - 13
							THEN 14400
							
							WHEN ST.CREATEDTIME BETWEEN 46800 AND 61200 -- START 13 - 17
							THEN 61200 - ST.CREATEDTIME
							
							WHEN ST.CREATEDTIME > 61200 -- START > 17
							THEN 0
							
							END
								+ 
								CASE WHEN CJ.CREATEDTIME < 28800 -- END < 8
								THEN 0
								
								WHEN CJ.CREATEDTIME BETWEEN 28800 AND 43200 --END 8 - 12
								THEN CJ.CREATEDTIME-28800
								
								WHEN CJ.CREATEDTIME BETWEEN 43200 AND 46800 -- END 12-13
								THEN 14400
								
								WHEN CJ.CREATEDTIME BETWEEN 46800 AND 61200 -- END 13-17
								THEN 61200 - CJ.CREATEDTIME
								
								WHEN CJ.CREATEDTIME > 61200 -- END > 17
								THEN 28800
								END
									
							+((28800*(DATEDIFF(DAY,ST.CREATEDDATE,CJ.CREATEDDATE)-1))-(28800 * DateDiff(ww, ST.CREATEDDATE, CJ.CREATEDDATE))) -- วันที่เหลือ - วันอาทิตย์
											
						
					ELSE 0 END [H]
					
					
					,CASE WHEN ISNULL(LINK.MAINSALESID, '') = '' THEN 0
						 ELSE 1
					END [CHILD]
					,ST.DSG_AVAILABLEDATE
					--,CASE WHEN @SECTION = 'A' THEN SQ.DSG_EDDDATE ELSE ST.DSG_EDDDATE END[EDDDATE]
					,ST.DSG_PRIMARYAGENTID
					,ST.DSG_LOADINGPLANT
					,ST.REMARKS
					,ST.DSG_REQUESTSHIPDATE
					,REF.SALESIDFACT
					,REF.COMPANYID
					,ST.DSG_REFCOMPANYID
					,CASE WHEN SQ.DSG_BOOKINGSTATUS = 1 THEN 'NotBooked'
						  WHEN SQ.DSG_BOOKINGSTATUS = 2 THEN 'Booked'
						  WHEN SQ.DSG_BOOKINGSTATUS = 3 THEN 'Confirmed'
						  WHEN SQ.DSG_BOOKINGSTATUS = 4 THEN 'Incorrect'
					ELSE '' END  [BOOKINGSTATUS]
					,CONVERT(varchar, DATEADD(ss, ST.CREATEDTIME, 0), 108) --START TIME
					,CONVERT(varchar, DATEADD(ss, CJ.CREATEDTIME, 0), 108) -- END TIME
					,CONVERT(varchar, DATEADD(ss, CJ.CREATEDTIME-ST.CREATEDTIME, 0), 108) -- NORMAL CAL
					,CONVERT(varchar, DATEADD(ss,  -- REAL CAL
					
					CASE WHEN ST.CREATEDDATE = CJ.CREATEDDATE AND DATEPART(DW, ST.CREATEDDATE)!= 1-- กรณี วันที่ Create SO และวันที่ Confirm SO เป็นเดียวกัน และไม่ใช่วันอาทิตย์
							THEN 
								CASE	WHEN ST.CREATEDTIME > 28800 AND CJ.CREATEDTIME < 61200 -- start และ end ในช่วง 8 - 17
								THEN 
								
									CASE	WHEN  ST.CREATEDTIME BETWEEN 43200 AND 46800  -- ทั้งสองอันอยู่ในช่วงเที่ยง
												AND CJ.CREATEDTIME BETWEEN 43200 AND 46800 
												THEN 0
											WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800  -- start ในช่วงเที่ยง end เกินเที่ยง
												AND CJ.CREATEDTIME NOT BETWEEN 43200 AND 46800 
											THEN  CJ.CREATEDTIME-46800 
											WHEN ST.CREATEDTIME NOT BETWEEN 43200 AND 46800  -- start ก่อนเที่ยง  end ในช่วงเที่ยง
												AND CJ.CREATEDTIME BETWEEN 43200 AND 46800
											THEN 43200-ST.CREATEDTIME 
											WHEN ST.CREATEDTIME < 43200  -- start ก่อนเที่ยง  end หลังเที่ยง
												AND CJ.CREATEDTIME > 46800  
											THEN (CJ.CREATEDTIME-ST.CREATEDTIME)-3600
									ELSE CJ.CREATEDTIME-ST.CREATEDTIME END
									
								WHEN ST.CREATEDTIME < 28800 AND CJ.CREATEDTIME BETWEEN 28800 AND 61200 -- START ก่อน 8 end ก่อน 17
								THEN 
								
									CASE	WHEN  CJ.CREATEDTIME < 43200  -- END ก่อนเที่ยง
											THEN  CJ.CREATEDTIME-28800 
											WHEN CJ.CREATEDTIME BETWEEN 43200 AND 46800 -- END ช่วงเที่ยง
											THEN 43200-28800 
											WHEN CJ.CREATEDTIME > 46800  -- END หลังเที่ยง
											THEN (CJ.CREATEDTIME-28800)-3600
									END
								WHEN ST.CREATEDTIME BETWEEN 28800 AND 61200 AND CJ.CREATEDTIME > 61200 -- START หลัง 8 end หลัง 17
								THEN 
								
									CASE	WHEN  ST.CREATEDTIME < 43200  -- START ก่อนเที่ยง
											THEN  (61200-ST.CREATEDTIME)-3600 
											WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800 -- START ช่วงเที่ยง
											THEN 61200-46800 
											WHEN CJ.CREATEDTIME > 46800  -- START หลังเที่ยง
											THEN 61200-ST.CREATEDTIME
									END
								WHEN ST.CREATEDTIME < 28800 AND CJ.CREATEDTIME > 61200 -- START ก่อน 8 end หลัง 17
								THEN 
											(61200-28800)-3600 
								ELSE 0
								END	
						WHEN ST.CREATEDDATE = CJ.CREATEDDATE AND DATEPART(DW, ST.CREATEDDATE)= 1 -- วันเดียวกัน แต่เป็นวันอาทิตย์
							THEN 0
						WHEN ST.CREATEDDATE != CJ.CREATEDDATE -- กรณีวันที่ Create SO และวันที่ Confirm SO ไม่ใช่วันที่เดียวกัน
							THEN 
								CASE WHEN ST.CREATEDTIME < 28800  -- START < 8  
								THEN 28800 	
								
								WHEN ST.CREATEDTIME BETWEEN 28800 AND 43200 -- START 8 - 12
								THEN 61200 - 3600 - ST.CREATEDTIME
								
								WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800 -- START 12 - 13
								THEN 14400
								
								WHEN ST.CREATEDTIME BETWEEN 46800 AND 61200 -- START 13 - 17
								THEN 61200 - ST.CREATEDTIME
								
								WHEN ST.CREATEDTIME > 61200 -- START > 17
								THEN 0
								
								END
									+ 
									CASE WHEN CJ.CREATEDTIME < 28800 -- END < 8
									THEN 0
									
									WHEN CJ.CREATEDTIME BETWEEN 28800 AND 43200 --END 8 - 12
									THEN CJ.CREATEDTIME-28800
									
									WHEN CJ.CREATEDTIME BETWEEN 43200 AND 46800 -- END 12-13
									THEN 14400
									
									WHEN CJ.CREATEDTIME BETWEEN 46800 AND 61200 -- END 13-17
									THEN 61200 - CJ.CREATEDTIME
									
									WHEN CJ.CREATEDTIME > 61200 -- END > 17
									THEN 28800
									END
										
								+((28800*(DATEDIFF(DAY,ST.CREATEDDATE,CJ.CREATEDDATE)-1))-(28800 * DateDiff(ww, ST.CREATEDDATE, CJ.CREATEDDATE))) -- วันที่เหลือ - วันอาทิตย์
								
						
					ELSE 0 END 
					, 0), 108)
					,QL.CUM 
					,CONVERT(NVARCHAR(1800),QT.DOCUINTRO) AS DOCUINTRO
					,CONVERT(NVARCHAR(1800),QT.DOCUCONCLUSION)--Nut CPM2019-1418 20190820
					,CJ.DSG_CONFIRMSOSTATUS
					,CASE 
						WHEN CJ.DSG_CONFIRMSOSTATUS = 1 THEN 'Waiting'
						WHEN CJ.DSG_CONFIRMSOSTATUS = 2 THEN 'Approved'
						WHEN CJ.DSG_CONFIRMSOSTATUS = 3 THEN 'Reject'
					ELSE 'NO STAUTS' END AS CONFIRMSOSTATUS
					,CJ.MODIFIEDBY	
					,(SELECT TOP 1 DSG_CONFIRMSOSTATUS FROM CUSTCONFIRMJOUR WHERE SALESID = CJ.SALESID AND DSG_CONFIRMSOSTATUS = 2) AS 'Revised'
					,SP.DSG_REASONREVISEORDERID
					,RVO.DSG_REASONREVISEORDERNAME
					,CJ.CONFIRMID
					,CUMX.CUM[CUMX] 

					FROM SALESTABLE ST
					LEFT JOIN SALESTABLELINKS LINK
						ON LINK.DATAAREAID = ST.DATAAREAID
						AND LINK.SUBSALESID = ST.SALESID
					LEFT JOIN SALESQUOTATIONTABLE QT
						ON QT.QUOTATIONID = ST.QUOTATIONID
						AND QT.DATAAREAID = ST.DATAAREAID
					LEFT JOIN (
								SELECT QL.QUOTATIONID,QL.DATAAREAID, SUM(QL.CUM)[CUM]
								FROM SALESQUOTATIONLINE QL
								GROUP BY QL.QUOTATIONID,QL.DATAAREAID
							   )QL
								ON QL.QUOTATIONID = QT.QUOTATIONID
								AND QL.DATAAREAID = QT.DATAAREAID
					LEFT JOIN CUSTCONFIRMJOUR CJ
						ON	CJ.SALESID		= ST.SALESID
						AND CJ.DATAAREAID	= ST.DATAAREAID
						AND CJ.CONFIRMDOCNUM = (
													SELECT CJ.SALESID + '-' + MAX(RIGHT(CJ.CONFIRMDOCNUM, 1)) [CONFIRMDOCNUM]
													FROM CUSTCONFIRMJOUR CJ
													WHERE CJ.SALESID	= ST.SALESID
													  AND CJ.DATAAREAID	= ST.DATAAREAID
													  AND CJ.DSG_CONFIRMSOSTATUS <> 0
													GROUP BY CJ.SALESID, CJ.DATAAREAID
												)
					LEFT JOIN USERINFO USERCON
						ON USERCON.ID	= CJ.CREATEDBY
					LEFT JOIN EMPLTABLE SALES
						ON SALES.EMPLID = ST.DSG_SALESREF AND Sales.DATAAREAID = CASE WHEN ST.DATAAREAID = 'DSC' THEN 'DSC' ELSE 'DV'END
					LEFT JOIN DSG_REFERENCEDSC REF
					ON	REF.SALESIDDSC	= CASE WHEN ST.DATAAREAID = 'DSC' THEN ST.SALESID ELSE '' END
					AND REF.DATAAREAID	= 'DC'
					LEFT JOIN SALESQUOTATIONTABLE SQ
					ON SQ.DATAAREAID = ST.DATAAREAID
					AND SQ.QUOTATIONID = ST.QUOTATIONID

					LEFT JOIN SALESPARMTABLE SP
					ON SP.SALESID = ST.SALESID
					AND SP.CREATEDDATE = CJ.CREATEDDATE
					AND SP.PARMID = CJ.PARMID
				    LEFT JOIN DSG_REASONREVISEORDER RVO 
						ON RVO.DSG_REASONREVISEORDERID = SP.DSG_REASONREVISEORDERID    

					LEFT JOIN 
					(
						SELECT 
						CJCUM.SALESID
						,CJCUM.CONFIRMID
						,SUM
						(
						CASE WHEN STCUM.CON = '1X20' THEN (CASE WHEN I.DSG_CUMLOAD20 != 0 THEN (CR.QTY * (30 / I.DSG_CUMLOAD20)) ELSE 0 END)
						WHEN STCUM.CON = '1x40' THEN (CASE WHEN I.DSG_CUMLOAD40 != 0 THEN (CR.QTY * (60 / I.DSG_CUMLOAD40)) ELSE 0 END)
						WHEN STCUM.CON = '1x40HC' THEN (CASE WHEN I.DSG_CUMLOAD40HC != 0 THEN (CR.QTY * (70 / I.DSG_CUMLOAD40HC)) ELSE 0 END)
						WHEN STCUM.CON = '1x45HC' THEN (CASE WHEN I.DSG_CUMLOAD45HC != 0 THEN (CR.QTY * (33 / I.DSG_CUMLOAD45HC)) ELSE 0 END)
						ELSE 0 END
						) [CUM]
						FROM CUSTCONFIRMJOUR CJCUM
						JOIN CUSTCONFIRMTRANS CR
						ON CR.SALESID		= CJCUM.SALESID
						AND CR.CONFIRMID	= CJCUM.CONFIRMID
						AND CR.CONFIRMDATE	= CJCUM.CONFIRMDATE
						AND CR.DATAAREAID	= CJCUM.DATAAREAID
						LEFT JOIN 
						(
							SELECT
								STCUM.SALESID
								,STCUM.DATAAREAID
								,CASE WHEN ((STCUM.[1X45HC] > STCUM.[1X40HC]) AND (STCUM.[1X45HC] > STCUM.[1X40]) AND (STCUM.[1X45HC] > STCUM.[1X20]))
										 THEN '1x45HC'
										 WHEN ((STCUM.[1X40HC] > STCUM.[1X45HC]) AND (STCUM.[1X40HC] > STCUM.[1X40]) AND (STCUM.[1X40HC] > STCUM.[1X20]))
										 THEN '1x40HC'
										 WHEN ((STCUM.[1X40] > STCUM.[1X45HC]) AND (STCUM.[1X40] > STCUM.[1X40HC]) AND (STCUM.[1X40] > STCUM.[1X20]))
										 THEN '1x40'
										 WHEN ((STCUM.[1X20] > STCUM.[1X45HC]) AND (STCUM.[1X20] >STCUM.[1X40HC]) AND (STCUM.[1X20] > STCUM.[1X40]))
										 THEN '1x20'
										 ELSE
										 (
											CASE WHEN STCUM.[1X45HC] != 0
												 THEN '1x45HC'
												 WHEN STCUM.[1X40HC] != 0
												 THEN '1x40HC'
												 WHEN STCUM.[1X40] != 0
												 THEN '1x40'
												 ELSE '1x20' END
										 ) END [CON]
								FROM
								(
									SELECT 
									STCUM.SALESID
									,STCUM.DATAAREAID
									,CASE WHEN STCUM.DSG_CONTAINER1X20 != 0 OR STCUM.DSG_CONTAINER1X40 != 0 OR STCUM.DSG_CONTAINER1X40HC != 0 OR STCUM.DSG_CONTAINER1X45HC != 0
									  THEN ISNULL(STCUM.DSG_CONTAINER1X20,0) / 2
									  ELSE
									  (
										CASE WHEN STRF.SALESID IS NOT NULL THEN ISNULL(STRF.DSG_CONTAINER1X20,0) / 2
											 ELSE (ISNULL(STC.DSG_CONTAINER1X20,0) + ISNULL(STRC.DSG_CONTAINER1X20,0) + ISNULL(STB.DSG_CONTAINER1X20,0)) / 2
											 END
									  )	  
									END [1X20]
									,CASE WHEN STCUM.DSG_CONTAINER1X20 != 0 OR STCUM.DSG_CONTAINER1X40 != 0 OR STCUM.DSG_CONTAINER1X40HC != 0 OR STCUM.DSG_CONTAINER1X45HC != 0
									  THEN ISNULL(STCUM.DSG_CONTAINER1X40,0)
									  ELSE
									  (
										CASE WHEN STRF.SALESID IS NOT NULL THEN ISNULL(STRF.DSG_CONTAINER1X40,0)
											 ELSE ISNULL(STC.DSG_CONTAINER1X40,0) + ISNULL(STRC.DSG_CONTAINER1X40,0) + ISNULL(STB.DSG_CONTAINER1X40,0)
											 END
									  )	  
									END [1X40]
									,CASE WHEN STCUM.DSG_CONTAINER1X20 != 0 OR STCUM.DSG_CONTAINER1X40 != 0 OR STCUM.DSG_CONTAINER1X40HC != 0 OR STCUM.DSG_CONTAINER1X45HC != 0
									  THEN ISNULL(STCUM.DSG_CONTAINER1X40HC,0)
									  ELSE
									  (
										CASE WHEN STRF.SALESID IS NOT NULL THEN ISNULL(STRF.DSG_CONTAINER1X40HC,0)
											 ELSE ISNULL(STC.DSG_CONTAINER1X40HC,0) + ISNULL(STRC.DSG_CONTAINER1X40HC,0) + ISNULL(STB.DSG_CONTAINER1X40HC,0)
											 END
									  )	  
									END [1X40HC]
									,CASE WHEN STCUM.DSG_CONTAINER1X20 != 0 OR STCUM.DSG_CONTAINER1X40 != 0 OR STCUM.DSG_CONTAINER1X40HC != 0 OR STCUM.DSG_CONTAINER1X45HC != 0
									  THEN ISNULL(STCUM.DSG_CONTAINER1X45HC,0)
									  ELSE
									  (
										CASE WHEN STRF.SALESID IS NOT NULL THEN ISNULL(STRF.DSG_CONTAINER1X45HC,0)
											 ELSE ISNULL(STC.DSG_CONTAINER1X45HC,0) + ISNULL(STRC.DSG_CONTAINER1X45HC,0) + ISNULL(STB.DSG_CONTAINER1X45HC,0)
											 END
									  )	  
									END [1X45HC]
									FROM SALESTABLE STCUM
									
									LEFT JOIN CUSTCONFIRMSALESLINK CK
									ON CK.ORIGSALESID		= STCUM.SALESID
									AND CK.SALESID			!= STCUM.SALESID
									AND CK.DATAAREAID		= STCUM.DATAAREAID

									LEFT JOIN DSG_SALESBOOKINGREF SF
									ON SF.DSG_SALESIDREF	= CK.SALESID
									AND SF.DATAAREAID		= CK.DATAAREAID

									LEFT JOIN SALESTABLE STC
									ON STC.SALESID			= CK.SALESID
									AND STC.DATAAREAID		= CK.DATAAREAID

									LEFT JOIN SALESTABLE STRC
									ON STRC.SALESID			= SF.DSG_SALESID
									AND STRC.DATAAREAID		= SF.DATAAREAID
									
									LEFT JOIN SALESTABLELINKS BK
									ON BK.SUBSALESID		= STCUM.SALESID
									AND BK.DATAAREAID		= STCUM.DATAAREAID

									LEFT JOIN SALESTABLE STB
									ON STB.SALESID			= BK.MAINSALESID
									AND STB.DATAAREAID		= BK.DATAAREAID
									LEFT JOIN 
									(
										SELECT SRF.DSG_SALESIDREF,SRF.DATAAREAID,STRF.SALESID
										,STRF.DSG_CONTAINER1X20
										,STRF.DSG_CONTAINER1X40
										,STRF.DSG_CONTAINER1X40HC
										,STRF.DSG_CONTAINER1X45HC
										,ROW_NUMBER() 
											OVER(
												PARTITION BY SRF.DSG_SALESIDREF,SRF.DATAAREAID
												ORDER BY STRF.SALESID DESC
												)AS NUM
										FROM DSG_SALESBOOKINGREF SRF
										LEFT JOIN SALESTABLE STRF
										ON STRF.SALESID			= SRF.DSG_SALESID
										AND STRF.DATAAREAID		= SRF.DATAAREAID
									)STRF
									ON STRF.DSG_SALESIDREF	= STCUM.SALESID
									AND STRF.DATAAREAID		= STCUM.DATAAREAID
									AND STRF.NUM			= 1
								)STCUM

							)STCUM
							ON STCUM.SALESID		= CR.SALESID
							AND STCUM.DATAAREAID	= CR.DATAAREAID
							JOIN INVENTTABLE I
							ON I.ITEMID			= CR.ITEMID
							AND I.DATAAREAID	= 'DSC'

					WHERE CJCUM.DATAAREAID	= 'DSC'
					GROUP BY 
					CJCUM.SALESID
					,CJCUM.CONFIRMID
					)CUMX ON CJ.SALESID = CUMX.SALESID  
						  AND CJ.CONFIRMID = CUMX.CONFIRMID

					WHERE ST.SALESID IN ($so)
					AND CJ.CUSTGROUP = 'OVS'
					ORDER BY CJ.MODIFIEDBY,ST.SALESID ASC";

			$data = Database::rows(
				$this->db_ax,
				$sql
			);

			return $data;
		} catch (\Exception $th) {
			return $e->getMessage();
		}
	}

	public function getBodyBySo($so) {
		try {

			$sql = "SELECT	
				CASE WHEN USERCON.NAME IS NULL THEN '' ELSE USERCON.NAME END [CONNAME]
				,ST.DATAAREAID,
				ST.SALESNAME [CUSTNAME],
				ST.DSG_TOPORTDESC [TOPORT],
				ST.QUOTATIONID,
				ST.SALESID,
				ST.CUSTOMERREF,
				ST.DSG_CONTAINER1X20 [CON20],
				ST.DSG_CONTAINER1X40 [CON40],
				ST.DSG_CONTAINER1X40HC [CON40HQ],
				ST.DSG_CONTAINER1X45HC [CON45HQ],
				SALES.NAME + ' ' + SALES.ALIAS [SALESNAME],
				ST.CREATEDDATE [PICONDATE],
				CONVERT(CHAR(8), DATEADD(SECOND, ST.CREATEDTIME, ''), 114) [PICONTIME],
				CJ.CREATEDDATE [SOCONDATE],
				CONVERT(CHAR(8), DATEADD(SECOND, CJ.CREATEDTIME, ''), 114) [SOCONTIME],
				CASE WHEN ST.DOCUMENTSTATUS = 0 THEN 1 ELSE 0 END [WIP_ECS],
				
				--- EDIT FOR 2019-719
				CASE WHEN ST.CREATEDDATE = CJ.CREATEDDATE -- กรณี วันที่ Create SO และวันที่ Confirm SO เป็นเดียวกัน
						THEN 
							CASE	WHEN ST.CREATEDTIME > 28800 AND CJ.CREATEDTIME < 61200 -- start และ end ในช่วง 8 - 17
							THEN 
								CASE	WHEN  ST.CREATEDTIME BETWEEN 43200 AND 46800  -- ทั้งสองอันอยู่ในช่วงเที่ยง
											AND CJ.CREATEDTIME BETWEEN 43200 AND 46800 
										THEN 0
										WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800  -- start ในช่วงเที่ยง end เกินเที่ยง
											AND CJ.CREATEDTIME NOT BETWEEN 43200 AND 46800 
										THEN  CJ.CREATEDTIME-46800 
										WHEN ST.CREATEDTIME NOT BETWEEN 43200 AND 46800  -- start ก่อนเที่ยง  end ในช่วงเที่ยง
											AND CJ.CREATEDTIME BETWEEN 43200 AND 46800
										THEN 43200-ST.CREATEDTIME 
										WHEN ST.CREATEDTIME < 43200  -- start ก่อนเที่ยง  end หลังเที่ยง
											AND CJ.CREATEDTIME > 46800  
										THEN (CJ.CREATEDTIME-ST.CREATEDTIME)-3600
								ELSE CJ.CREATEDTIME-ST.CREATEDTIME END
											
							WHEN ST.CREATEDTIME < 28800 AND CJ.CREATEDTIME BETWEEN 28800 AND 61200 -- START ก่อน 8 end ก่อน 17
							THEN 
							
								CASE	WHEN  CJ.CREATEDTIME < 43200  -- END ก่อนเที่ยง
										THEN  CJ.CREATEDTIME-28800 
										WHEN CJ.CREATEDTIME BETWEEN 43200 AND 46800 -- END ช่วงเที่ยง
										THEN 43200-28800 
										WHEN CJ.CREATEDTIME > 46800  -- END หลังเที่ยง
										THEN (CJ.CREATEDTIME-28800)-3600
								END
							WHEN ST.CREATEDTIME BETWEEN 28800 AND 61200 AND CJ.CREATEDTIME > 61200 -- START หลัง 8 end หลัง 17
							THEN 
							
								CASE	WHEN  ST.CREATEDTIME < 43200  -- START ก่อนเที่ยง
										THEN  (61200-ST.CREATEDTIME)-3600 
										WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800 -- START ช่วงเที่ยง
										THEN 61200-46800 
										WHEN CJ.CREATEDTIME > 46800  -- START หลังเที่ยง
										THEN 61200-ST.CREATEDTIME
								END
							WHEN ST.CREATEDTIME < 28800 AND CJ.CREATEDTIME > 61200 -- START ก่อน 8 end หลัง 17
							THEN 
										(61200-28800)-3600 
							ELSE 0
							END	
					WHEN ST.CREATEDDATE = CJ.CREATEDDATE AND DATEPART(DW, ST.CREATEDDATE)= 1 -- วันเดียวกัน แต่เป็นวันอาทิตย์
						THEN 0
						
					WHEN ST.CREATEDDATE != CJ.CREATEDDATE -- กรณีวันที่ Create SO และวันที่ Confirm SO ไม่ใช่วันที่เดียวกัน
						THEN 
							CASE WHEN ST.CREATEDTIME < 28800  -- START < 8  
						THEN 28800 	
						
						WHEN ST.CREATEDTIME BETWEEN 28800 AND 43200 -- START 8 - 12
						THEN 61200 - 3600 - ST.CREATEDTIME
						
						WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800 -- START 12 - 13
						THEN 14400
						
						WHEN ST.CREATEDTIME BETWEEN 46800 AND 61200 -- START 13 - 17
						THEN 61200 - ST.CREATEDTIME
						
						WHEN ST.CREATEDTIME > 61200 -- START > 17
						THEN 0
						
						END
							+ 
							CASE WHEN CJ.CREATEDTIME < 28800 -- END < 8
							THEN 0
							
							WHEN CJ.CREATEDTIME BETWEEN 28800 AND 43200 --END 8 - 12
							THEN CJ.CREATEDTIME-28800
							
							WHEN CJ.CREATEDTIME BETWEEN 43200 AND 46800 -- END 12-13
							THEN 14400
							
							WHEN CJ.CREATEDTIME BETWEEN 46800 AND 61200 -- END 13-17
							THEN 61200 - CJ.CREATEDTIME
							
							WHEN CJ.CREATEDTIME > 61200 -- END > 17
							THEN 28800
							END
								
						+((28800*(DATEDIFF(DAY,ST.CREATEDDATE,CJ.CREATEDDATE)-1))-(28800 * DateDiff(ww, ST.CREATEDDATE, CJ.CREATEDDATE))) -- วันที่เหลือ - วันอาทิตย์
										
					
				ELSE 0 END [H]
				
				
				,CASE WHEN ISNULL(LINK.MAINSALESID, '') = '' THEN 0
					 ELSE 1
				END [CHILD]
				,ST.DSG_AVAILABLEDATE
				--,CASE WHEN @SECTION = 'A' THEN SQ.DSG_EDDDATE ELSE ST.DSG_EDDDATE END[EDDDATE]
				,ST.DSG_PRIMARYAGENTID
				,ST.DSG_LOADINGPLANT
				,ST.REMARKS
				,ST.DSG_REQUESTSHIPDATE
				,REF.SALESIDFACT
				,REF.COMPANYID
				,ST.DSG_REFCOMPANYID
				,CASE WHEN SQ.DSG_BOOKINGSTATUS = 1 THEN 'NotBooked'
					  WHEN SQ.DSG_BOOKINGSTATUS = 2 THEN 'Booked'
					  WHEN SQ.DSG_BOOKINGSTATUS = 3 THEN 'Confirmed'
					  WHEN SQ.DSG_BOOKINGSTATUS = 4 THEN 'Incorrect'
				ELSE '' END  [BOOKINGSTATUS]
				,CONVERT(varchar, DATEADD(ss, ST.CREATEDTIME, 0), 108) --START TIME
				,CONVERT(varchar, DATEADD(ss, CJ.CREATEDTIME, 0), 108) -- END TIME
				,CONVERT(varchar, DATEADD(ss, CJ.CREATEDTIME-ST.CREATEDTIME, 0), 108) -- NORMAL CAL
				,CONVERT(varchar, DATEADD(ss,  -- REAL CAL
				
				CASE WHEN ST.CREATEDDATE = CJ.CREATEDDATE AND DATEPART(DW, ST.CREATEDDATE)!= 1-- กรณี วันที่ Create SO และวันที่ Confirm SO เป็นเดียวกัน และไม่ใช่วันอาทิตย์
						THEN 
							CASE	WHEN ST.CREATEDTIME > 28800 AND CJ.CREATEDTIME < 61200 -- start และ end ในช่วง 8 - 17
							THEN 
							
								CASE	WHEN  ST.CREATEDTIME BETWEEN 43200 AND 46800  -- ทั้งสองอันอยู่ในช่วงเที่ยง
											AND CJ.CREATEDTIME BETWEEN 43200 AND 46800 
											THEN 0
										WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800  -- start ในช่วงเที่ยง end เกินเที่ยง
											AND CJ.CREATEDTIME NOT BETWEEN 43200 AND 46800 
										THEN  CJ.CREATEDTIME-46800 
										WHEN ST.CREATEDTIME NOT BETWEEN 43200 AND 46800  -- start ก่อนเที่ยง  end ในช่วงเที่ยง
											AND CJ.CREATEDTIME BETWEEN 43200 AND 46800
										THEN 43200-ST.CREATEDTIME 
										WHEN ST.CREATEDTIME < 43200  -- start ก่อนเที่ยง  end หลังเที่ยง
											AND CJ.CREATEDTIME > 46800  
										THEN (CJ.CREATEDTIME-ST.CREATEDTIME)-3600
								ELSE CJ.CREATEDTIME-ST.CREATEDTIME END
								
							WHEN ST.CREATEDTIME < 28800 AND CJ.CREATEDTIME BETWEEN 28800 AND 61200 -- START ก่อน 8 end ก่อน 17
							THEN 
							
								CASE	WHEN  CJ.CREATEDTIME < 43200  -- END ก่อนเที่ยง
										THEN  CJ.CREATEDTIME-28800 
										WHEN CJ.CREATEDTIME BETWEEN 43200 AND 46800 -- END ช่วงเที่ยง
										THEN 43200-28800 
										WHEN CJ.CREATEDTIME > 46800  -- END หลังเที่ยง
										THEN (CJ.CREATEDTIME-28800)-3600
								END
							WHEN ST.CREATEDTIME BETWEEN 28800 AND 61200 AND CJ.CREATEDTIME > 61200 -- START หลัง 8 end หลัง 17
							THEN 
							
								CASE	WHEN  ST.CREATEDTIME < 43200  -- START ก่อนเที่ยง
										THEN  (61200-ST.CREATEDTIME)-3600 
										WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800 -- START ช่วงเที่ยง
										THEN 61200-46800 
										WHEN CJ.CREATEDTIME > 46800  -- START หลังเที่ยง
										THEN 61200-ST.CREATEDTIME
								END
							WHEN ST.CREATEDTIME < 28800 AND CJ.CREATEDTIME > 61200 -- START ก่อน 8 end หลัง 17
							THEN 
										(61200-28800)-3600 
							ELSE 0
							END	
					WHEN ST.CREATEDDATE = CJ.CREATEDDATE AND DATEPART(DW, ST.CREATEDDATE)= 1 -- วันเดียวกัน แต่เป็นวันอาทิตย์
						THEN 0
					WHEN ST.CREATEDDATE != CJ.CREATEDDATE -- กรณีวันที่ Create SO และวันที่ Confirm SO ไม่ใช่วันที่เดียวกัน
						THEN 
							CASE WHEN ST.CREATEDTIME < 28800  -- START < 8  
							THEN 28800 	
							
							WHEN ST.CREATEDTIME BETWEEN 28800 AND 43200 -- START 8 - 12
							THEN 61200 - 3600 - ST.CREATEDTIME
							
							WHEN ST.CREATEDTIME BETWEEN 43200 AND 46800 -- START 12 - 13
							THEN 14400
							
							WHEN ST.CREATEDTIME BETWEEN 46800 AND 61200 -- START 13 - 17
							THEN 61200 - ST.CREATEDTIME
							
							WHEN ST.CREATEDTIME > 61200 -- START > 17
							THEN 0
							
							END
								+ 
								CASE WHEN CJ.CREATEDTIME < 28800 -- END < 8
								THEN 0
								
								WHEN CJ.CREATEDTIME BETWEEN 28800 AND 43200 --END 8 - 12
								THEN CJ.CREATEDTIME-28800
								
								WHEN CJ.CREATEDTIME BETWEEN 43200 AND 46800 -- END 12-13
								THEN 14400
								
								WHEN CJ.CREATEDTIME BETWEEN 46800 AND 61200 -- END 13-17
								THEN 61200 - CJ.CREATEDTIME
								
								WHEN CJ.CREATEDTIME > 61200 -- END > 17
								THEN 28800
								END
									
							+((28800*(DATEDIFF(DAY,ST.CREATEDDATE,CJ.CREATEDDATE)-1))-(28800 * DateDiff(ww, ST.CREATEDDATE, CJ.CREATEDDATE))) -- วันที่เหลือ - วันอาทิตย์
							
					
				ELSE 0 END 
				, 0), 108)
				,QL.CUM 
				,CONVERT(NVARCHAR(1800),QT.DOCUINTRO) AS DOCUINTRO
				,CONVERT(NVARCHAR(1800),QT.DOCUCONCLUSION)--Nut CPM2019-1418 20190820
				,CJ.DSG_CONFIRMSOSTATUS
				,CASE 
					WHEN CJ.DSG_CONFIRMSOSTATUS = 1 THEN 'Waiting'
					WHEN CJ.DSG_CONFIRMSOSTATUS = 2 THEN 'Approved'
					WHEN CJ.DSG_CONFIRMSOSTATUS = 3 THEN 'Reject'
				ELSE 'NO STAUTS' END AS CONFIRMSOSTATUS
				,CJ.MODIFIEDBY	
				,(SELECT TOP 1 DSG_CONFIRMSOSTATUS FROM CUSTCONFIRMJOUR WHERE SALESID = CJ.SALESID AND DSG_CONFIRMSOSTATUS = 2) AS 'Revised'
				,SP.DSG_REASONREVISEORDERID
				,RVO.DSG_REASONREVISEORDERNAME
				,CJ.CONFIRMID
				,CUMX.CUM[CUMX] 

				FROM SALESTABLE ST
				LEFT JOIN SALESTABLELINKS LINK
					ON LINK.DATAAREAID = ST.DATAAREAID
					AND LINK.SUBSALESID = ST.SALESID
				LEFT JOIN SALESQUOTATIONTABLE QT
					ON QT.QUOTATIONID = ST.QUOTATIONID
					AND QT.DATAAREAID = ST.DATAAREAID
				LEFT JOIN (
							SELECT QL.QUOTATIONID,QL.DATAAREAID, SUM(QL.CUM)[CUM]
							FROM SALESQUOTATIONLINE QL
							GROUP BY QL.QUOTATIONID,QL.DATAAREAID
						   )QL
							ON QL.QUOTATIONID = QT.QUOTATIONID
							AND QL.DATAAREAID = QT.DATAAREAID
				LEFT JOIN CUSTCONFIRMJOUR CJ
					ON	CJ.SALESID		= ST.SALESID
					AND CJ.DATAAREAID	= ST.DATAAREAID
					AND CJ.CONFIRMDOCNUM = (
												SELECT CJ.SALESID + '-' + MAX(RIGHT(CJ.CONFIRMDOCNUM, 1)) [CONFIRMDOCNUM]
												FROM CUSTCONFIRMJOUR CJ
												WHERE CJ.SALESID	= ST.SALESID
												  AND CJ.DATAAREAID	= ST.DATAAREAID
												  AND CJ.DSG_CONFIRMSOSTATUS <> 0
												GROUP BY CJ.SALESID, CJ.DATAAREAID
											)
				LEFT JOIN USERINFO USERCON
					ON USERCON.ID	= CJ.CREATEDBY
				LEFT JOIN EMPLTABLE SALES
					ON SALES.EMPLID = ST.DSG_SALESREF AND Sales.DATAAREAID = CASE WHEN ST.DATAAREAID = 'DSC' THEN 'DSC' ELSE 'DV'END
				LEFT JOIN DSG_REFERENCEDSC REF
				ON	REF.SALESIDDSC	= CASE WHEN ST.DATAAREAID = 'DSC' THEN ST.SALESID ELSE '' END
				AND REF.DATAAREAID	= 'DC'
				LEFT JOIN SALESQUOTATIONTABLE SQ
				ON SQ.DATAAREAID = ST.DATAAREAID
				AND SQ.QUOTATIONID = ST.QUOTATIONID 

				LEFT JOIN SALESPARMTABLE SP
					ON SP.SALESID = ST.SALESID
					AND SP.CREATEDDATE = CJ.CREATEDDATE
					AND SP.PARMID = CJ.PARMID
			    LEFT JOIN DSG_REASONREVISEORDER RVO 
					ON RVO.DSG_REASONREVISEORDERID = SP.DSG_REASONREVISEORDERID    

				LEFT JOIN 
				(
					SELECT 
					CJCUM.SALESID
					,CJCUM.CONFIRMID
					,SUM
					(
					CASE WHEN STCUM.CON = '1X20' THEN (CASE WHEN I.DSG_CUMLOAD20 != 0 THEN (CR.QTY * (30 / I.DSG_CUMLOAD20)) ELSE 0 END)
					WHEN STCUM.CON = '1x40' THEN (CASE WHEN I.DSG_CUMLOAD40 != 0 THEN (CR.QTY * (60 / I.DSG_CUMLOAD40)) ELSE 0 END)
					WHEN STCUM.CON = '1x40HC' THEN (CASE WHEN I.DSG_CUMLOAD40HC != 0 THEN (CR.QTY * (70 / I.DSG_CUMLOAD40HC)) ELSE 0 END)
					WHEN STCUM.CON = '1x45HC' THEN (CASE WHEN I.DSG_CUMLOAD45HC != 0 THEN (CR.QTY * (33 / I.DSG_CUMLOAD45HC)) ELSE 0 END)
					ELSE 0 END
					) [CUM]
					FROM CUSTCONFIRMJOUR CJCUM
					JOIN CUSTCONFIRMTRANS CR
					ON CR.SALESID		= CJCUM.SALESID
					AND CR.CONFIRMID	= CJCUM.CONFIRMID
					AND CR.CONFIRMDATE	= CJCUM.CONFIRMDATE
					AND CR.DATAAREAID	= CJCUM.DATAAREAID
					LEFT JOIN 
					(
						SELECT
							STCUM.SALESID
							,STCUM.DATAAREAID
							,CASE WHEN ((STCUM.[1X45HC] > STCUM.[1X40HC]) AND (STCUM.[1X45HC] > STCUM.[1X40]) AND (STCUM.[1X45HC] > STCUM.[1X20]))
									 THEN '1x45HC'
									 WHEN ((STCUM.[1X40HC] > STCUM.[1X45HC]) AND (STCUM.[1X40HC] > STCUM.[1X40]) AND (STCUM.[1X40HC] > STCUM.[1X20]))
									 THEN '1x40HC'
									 WHEN ((STCUM.[1X40] > STCUM.[1X45HC]) AND (STCUM.[1X40] > STCUM.[1X40HC]) AND (STCUM.[1X40] > STCUM.[1X20]))
									 THEN '1x40'
									 WHEN ((STCUM.[1X20] > STCUM.[1X45HC]) AND (STCUM.[1X20] >STCUM.[1X40HC]) AND (STCUM.[1X20] > STCUM.[1X40]))
									 THEN '1x20'
									 ELSE
									 (
										CASE WHEN STCUM.[1X45HC] != 0
											 THEN '1x45HC'
											 WHEN STCUM.[1X40HC] != 0
											 THEN '1x40HC'
											 WHEN STCUM.[1X40] != 0
											 THEN '1x40'
											 ELSE '1x20' END
									 ) END [CON]
							FROM
							(
								SELECT 
								STCUM.SALESID
								,STCUM.DATAAREAID
								,CASE WHEN STCUM.DSG_CONTAINER1X20 != 0 OR STCUM.DSG_CONTAINER1X40 != 0 OR STCUM.DSG_CONTAINER1X40HC != 0 OR STCUM.DSG_CONTAINER1X45HC != 0
								  THEN ISNULL(STCUM.DSG_CONTAINER1X20,0) / 2
								  ELSE
								  (
									CASE WHEN STRF.SALESID IS NOT NULL THEN ISNULL(STRF.DSG_CONTAINER1X20,0) / 2
										 ELSE (ISNULL(STC.DSG_CONTAINER1X20,0) + ISNULL(STRC.DSG_CONTAINER1X20,0) + ISNULL(STB.DSG_CONTAINER1X20,0)) / 2
										 END
								  )	  
								END [1X20]
								,CASE WHEN STCUM.DSG_CONTAINER1X20 != 0 OR STCUM.DSG_CONTAINER1X40 != 0 OR STCUM.DSG_CONTAINER1X40HC != 0 OR STCUM.DSG_CONTAINER1X45HC != 0
								  THEN ISNULL(STCUM.DSG_CONTAINER1X40,0)
								  ELSE
								  (
									CASE WHEN STRF.SALESID IS NOT NULL THEN ISNULL(STRF.DSG_CONTAINER1X40,0)
										 ELSE ISNULL(STC.DSG_CONTAINER1X40,0) + ISNULL(STRC.DSG_CONTAINER1X40,0) + ISNULL(STB.DSG_CONTAINER1X40,0)
										 END
								  )	  
								END [1X40]
								,CASE WHEN STCUM.DSG_CONTAINER1X20 != 0 OR STCUM.DSG_CONTAINER1X40 != 0 OR STCUM.DSG_CONTAINER1X40HC != 0 OR STCUM.DSG_CONTAINER1X45HC != 0
								  THEN ISNULL(STCUM.DSG_CONTAINER1X40HC,0)
								  ELSE
								  (
									CASE WHEN STRF.SALESID IS NOT NULL THEN ISNULL(STRF.DSG_CONTAINER1X40HC,0)
										 ELSE ISNULL(STC.DSG_CONTAINER1X40HC,0) + ISNULL(STRC.DSG_CONTAINER1X40HC,0) + ISNULL(STB.DSG_CONTAINER1X40HC,0)
										 END
								  )	  
								END [1X40HC]
								,CASE WHEN STCUM.DSG_CONTAINER1X20 != 0 OR STCUM.DSG_CONTAINER1X40 != 0 OR STCUM.DSG_CONTAINER1X40HC != 0 OR STCUM.DSG_CONTAINER1X45HC != 0
								  THEN ISNULL(STCUM.DSG_CONTAINER1X45HC,0)
								  ELSE
								  (
									CASE WHEN STRF.SALESID IS NOT NULL THEN ISNULL(STRF.DSG_CONTAINER1X45HC,0)
										 ELSE ISNULL(STC.DSG_CONTAINER1X45HC,0) + ISNULL(STRC.DSG_CONTAINER1X45HC,0) + ISNULL(STB.DSG_CONTAINER1X45HC,0)
										 END
								  )	  
								END [1X45HC]
								FROM SALESTABLE STCUM
								
								LEFT JOIN CUSTCONFIRMSALESLINK CK
								ON CK.ORIGSALESID		= STCUM.SALESID
								AND CK.SALESID			!= STCUM.SALESID
								AND CK.DATAAREAID		= STCUM.DATAAREAID

								LEFT JOIN DSG_SALESBOOKINGREF SF
								ON SF.DSG_SALESIDREF	= CK.SALESID
								AND SF.DATAAREAID		= CK.DATAAREAID

								LEFT JOIN SALESTABLE STC
								ON STC.SALESID			= CK.SALESID
								AND STC.DATAAREAID		= CK.DATAAREAID

								LEFT JOIN SALESTABLE STRC
								ON STRC.SALESID			= SF.DSG_SALESID
								AND STRC.DATAAREAID		= SF.DATAAREAID
								
								LEFT JOIN SALESTABLELINKS BK
								ON BK.SUBSALESID		= STCUM.SALESID
								AND BK.DATAAREAID		= STCUM.DATAAREAID

								LEFT JOIN SALESTABLE STB
								ON STB.SALESID			= BK.MAINSALESID
								AND STB.DATAAREAID		= BK.DATAAREAID
								LEFT JOIN 
								(
									SELECT SRF.DSG_SALESIDREF,SRF.DATAAREAID,STRF.SALESID
									,STRF.DSG_CONTAINER1X20
									,STRF.DSG_CONTAINER1X40
									,STRF.DSG_CONTAINER1X40HC
									,STRF.DSG_CONTAINER1X45HC
									,ROW_NUMBER() 
										OVER(
											PARTITION BY SRF.DSG_SALESIDREF,SRF.DATAAREAID
											ORDER BY STRF.SALESID DESC
											)AS NUM
									FROM DSG_SALESBOOKINGREF SRF
									LEFT JOIN SALESTABLE STRF
									ON STRF.SALESID			= SRF.DSG_SALESID
									AND STRF.DATAAREAID		= SRF.DATAAREAID
								)STRF
								ON STRF.DSG_SALESIDREF	= STCUM.SALESID
								AND STRF.DATAAREAID		= STCUM.DATAAREAID
								AND STRF.NUM			= 1
							)STCUM

						)STCUM
						ON STCUM.SALESID		= CR.SALESID
						AND STCUM.DATAAREAID	= CR.DATAAREAID
						JOIN INVENTTABLE I
						ON I.ITEMID			= CR.ITEMID
						AND I.DATAAREAID	= 'DSC'

					WHERE CJCUM.DATAAREAID	= 'DSC'
					GROUP BY 
					CJCUM.SALESID
					,CJCUM.CONFIRMID
					)CUMX ON CJ.SALESID = CUMX.SALESID  
						  AND CJ.CONFIRMID = CUMX.CONFIRMID

				WHERE ST.SALESID IN ($so)
				AND CJ.CUSTGROUP = 'OVS'
				ORDER BY CJ.MODIFIEDBY ASC";
			// return $sql;

			$data = Database::rows(
				$this->db_ax,
				$sql
			);

			$txt = '';

			$txt .= '<style>
					table, th, td { 
						border-collapse: collapse; 
						border: 1px solid #b2b2b2;
						font-family: "Cordia New";
						font-size: 22px;
					}
					label,a {
						font-family: "Cordia New";
						font-size: 22px;
					}
					</style>';

			$txt .= '<table>';
			$txt .= '<tr style="background-color: #d9edf6;">';
			$txt .= '<td valign="top"><label>CUSTNAME</label></td>';
			$txt .= '<td valign="top"><label>TOPORT</label></td>';
			$txt .= '<td valign="top"><label>QUOTATION ID</label></td>';
			$txt .= '<td valign="top"><label>SALES ID</label></td>';
			// $txt .= '<td valign="top"><label>SALESID FACTORY</label></td>';
			// $txt .= '<td valign="top"><label>FACTORY</label></td>';
			$txt .= '<td valign="top"><label>ORDER OF COMPANY</label></td>';
			$txt .= '<td valign="top"><label>CUSTOMER REF.</label></td>';
			$txt .= '<td valign="top"><label>TOTAL CU.M</label></td>';
			$txt .= '<td valign="top"><label>20\' FCL</label></td>';
			$txt .= '<td valign="top"><label>40\' FCL</label></td>';
			$txt .= '<td valign="top"><label>40\' HQ</label></td>';
			// $txt .= '<td valign="top"><label>REMARK (SO)</label></td>';
			// $txt .= '<td valign="top"><label>REMARK (PI)</label></td>';
			$txt .= '<td valign="top"><label>REQUEST SHIP DATE</label></td>';
			$txt .= '<td valign="top"><label>SALE NAME</label></td>';
			$txt .= '<td valign="top"><label>SO CONFIRM DATE</label></td>';
			$txt .= '<td valign="top"><label>DATA ENTRY</label></td>';
			$txt .= '</tr>';

			foreach ($data as $key => $value) {  
	 			$txt .= "<tr>";
	 			$txt .= "<td>".$value['CUSTNAME']."</td>";
	 			$txt .= "<td>".$value['TOPORT']."</td>";
	 			$txt .= "<td>".$value['QUOTATIONID']."</td>";
	 			$txt .= "<td>".$value['SALESID']."</td>";
	 			// $txt .= "<td>".$value['SALESIDFACT']."</td>";
	 			// $txt .= "<td>".$value['COMPANYID']."</td>";
	 			$txt .= "<td>".$value['DSG_REFCOMPANYID']."</td>";
	 			$txt .= "<td>".$value['CUSTOMERREF']."</td>";
	 			$txt .= "<td>".$value['CUMX']."</td>";
	 			$txt .= "<td>".number_format($value['CON20'])."</td>";
	 			$txt .= "<td>".number_format($value['CON40'])."</td>";
	 			$txt .= "<td>".number_format($value['CON40HQ'])."</td>";
	 			// $txt .= "<td>".$value['REMARKS']."</td>";
	 			// $txt .= "<td>".$value['DOCUINTRO']."</td>";
	 			$txt .= "<td>".$value['DSG_REQUESTSHIPDATE']."</td>";
	 			$txt .= "<td>".$value['SALESNAME']."</td>";
	 			$txt .= "<td>".date("d/m/Y", strtotime($value['SOCONDATE']))."</td>";
	 			$txt .= "<td>".$value['CONNAME']."</td>";
	 			$txt .= "</tr>";
	 		} 

			$txt .= '</table>';
			return $txt;

		} catch (\Exception $th) {
			return $e->getMessage();
		}
	}

	public function getEmailConfirm()
	{
		try {
			return Database::rows(
				$this->db_ax,
				"SELECT USERNAME,USERID,EMAIL 
				FROM DSG_ConfirmSOCheck 
				GROUP BY USERNAME,USERID,EMAIL
				ORDER BY USERID ASC"
			);

		} catch (\Exception $e) {
			return [];
		}
	}

	public function getEmailConfirmBy($userid)
	{
		try {
			return Database::rows(
				$this->db_ax,
				"SELECT USERNAME,USERID,EMAIL 
				FROM DSG_ConfirmSOCheck 
				WHERE USERID = ?
				GROUP BY USERNAME,USERID,EMAIL
				ORDER BY USERID ASC",[$userid]
			);

		} catch (\Exception $e) {
			return [];
		}
	}

	public function getEmailApprove()
	{
		try {
			return Database::rows(
				$this->db_ax,
				"SELECT TOP 1 USERNAME,EMAIL
				FROM DSG_ConfirmSOApprove 
				GROUP BY USERNAME,EMAIL"
			);

		} catch (\Exception $e) {
			return [];
		}
	}

	public function updateStatus($newstatus,$oldstatus,$so)
	{
		try {
			// return Database::rows(
			// 	$this->db_ax,
			// 	"UPDATE CustConfirmJour 
			// 	SET DSG_ConfirmSOStatus = ? , DSG_ConfirmSOApproveDate = getdate()
			// 	WHERE DSG_CONFIRMSOSTATUS = ? AND CUSTGROUP = ?
			// 	AND SALESID = ?",[$newstatus,$oldstatus,'OVS',$so]
			// );
			return Database::rows(
				$this->db_ax,
				"UPDATE CustConfirmJour 
				SET DSG_ConfirmSOStatus = ? , DSG_ConfirmSOApproveDate = getdate()
				WHERE DATAAREAID = 'DSC' AND
				DSG_CONFIRMSOSTATUS = ? AND CUSTGROUP = ?
				AND SALESID IN ($so)",[$newstatus,$oldstatus,'OVS']
			);

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function insertNonce($noncekey,$nonce)
	{
		try {
			return Database::rows(
				$this->db_live,
				"INSERT INTO web_nonce (nonce_key,nonce,used,create_date)
				VALUES (?,?,?,getdate())",[$noncekey,$nonce,0]
			);

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function updateNonce($email,$nonce)
	{
		try {
			return Database::rows(
				$this->db_live,
				"UPDATE web_nonce 
				SET used = ? , update_date = getdate()
				WHERE nonce = ?
				AND nonce_key = ?",[1,$nonce,$email]
			);

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function checknonce($nonce)
	{
		try {

			$check = Database::hasRows(
				$this->db_live,
				"SELECT *
				FROM web_nonce
				WHERE nonce = ?",[$nonce]
			);

			if ($check) {
				
				$query = Database::rows(
					$this->db_live,
					"SELECT *
					FROM web_nonce
					WHERE nonce = ?",[$nonce]
				);

				$date = $query[0]['create_date'];
				$daynonce = date('Y-m-d H:i:s',strtotime($date . "+14 days"));

				$datenow = date('Y-m-d H:i:s');

				if ($query[0]['used']==1) {
					return ["status" => 404, "message" => "Your link has approved !"];
				}else if ($daynonce<$datenow) {
					return ["status" => 404, "message" => "Your link has expired !"];
				}else{
					return ["status" => 200, "message" => "Your link has ready !"];
				}			
			
			}else{
				return ["status" => 404, "message" => "Your link has not found !"];
			}
			

		} catch (\Exception $e) {
			return [];
		}
	}	

	public function insertLogs($userid,$listsoall,$type)
	{
		try {
			// for ($i=0; $i < count($listsoall); $i++) { 
			foreach ($listsoall as $value) {
				$insert = Database::rows(
					$this->db_live,
					"INSERT INTO ApproveLogs (SaleId,ConfirmId,ApproveDate,Status,Type)
					VALUES (?,?,getdate(),?,?)",[$value,$userid,1,$type]
				);
			}

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getLogs($userid,$type,$today)
	{
		try {
			$sql = Database::rows(
				$this->db_live,
				"SELECT SaleId,ConfirmId
				  FROM ApproveLogs
				  WHERE Status=? AND ConfirmId=? AND Type=? AND CONVERT(DATE, ApproveDate) = ?
				  GROUP BY SaleId,ConfirmId",[1,$userid,$type,$today]
			);

			$so=[];
			foreach ($sql as $key => $value) {
				array_push($so, $value['SaleId']);
			}
			return $so;
		} catch (\Exception $e) {
			return [];
		}
	}

}