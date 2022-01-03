<?php 

namespace App\Releasedoc;

use App\Common\Database;
use Webmozart\Assert\Assert;

class ReleasedocAPI {

	public function __construct() {
		$this->db = Database::connect();
		$this->db_ax = Database::connect('ax');
	}

	public function getLogs($filter) {
		try {
			return Database::rows(
				$this->db,
				"SELECT TOP 50 L.ID,L.ProjectID,L.Message,L.Source,P.ProjectName,L.SendDate,L.CustomerCode,L.SO,L.Invoice
				FROM Logs L
				LEFT JOIN Project P ON L.ProjectID=P.ProjectID
				WHERE L.ProjectID=? AND $filter
				ORDER BY L.ID DESC",
				[
					28
				]
			);
		} catch (\Exception $e) {
			throw new \Exception('Error: Query error.');
		}
	}

	public function getWaiting($filter) {
		try {
			return Database::rows(
				$this->db_ax,
				"SELECT 
			      L.DSG_SALESID AS So,
			      J.OrderAccount +' '+ 'Invoice No.' +J.Series+'/'+UPPER(L.DSG_DATAAREAID)+'/'+ CONVERT(varchar(10),J.Voucher_no) AS 'Release',
			      CONVERT(date, L.createdDate) AS CreateDate,
			      L.DSG_SALESID,
			      J.OrderAccount,
			      C.NAME,
			      J.Series,
			      J.Voucher_no,
			      CASE 
			        WHEN L.CREATEDBY = 'auto' THEN 'Auto under Credit Control'
			      ELSE U.NAME END [FULLNAME],
			      L.DSG_AfterValue,
			      L.createdDate,
			      L.createdTime,
			      L.DSG_PACKINGSLIPID,
			      CASE 
			        WHEN L.DSG_DATAAREAID = 'dsr' THEN 'SVO'
			      ELSE UPPER(L.DSG_DATAAREAID) END [DSG_DATAAREAID] ,J.DSG_EL_by,J.IV
			      FROM  DSG_SalesLog L
			      LEFT JOIN CustPackingSlipJour J ON L.DSG_SALESID=J.SALESID AND L.DSG_DATAAREAID=J.DATAAREAID AND L.DSG_PACKINGSLIPID=J.LEDGERVOUCHER
			      LEFT JOIN CustTable C ON J.OrderAccount=C.ACCOUNTNUM AND L.DATAAREAID=C.DATAAREAID
			      LEFT JOIN USERINFO U ON L.CREATEDBY=U.ID AND U.ENABLE=1
			      WHERE L.DSG_SentEmail=1 
			      AND L.DSG_SALESLOGCATEGORY=11 
			      AND L.DSG_DATAAREAID='DSC'
			      ORDER BY L.createdDate DESC"
			);
		} catch (\Exception $e) {
			throw new \Exception('Error: Query error.');
		}	
	}

}