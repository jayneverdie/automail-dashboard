<?php

namespace App\ShortShip;

use App\Common\Database;

class ShortShipAPI
{
  private $db_ax = null;
  private $db_live = null;

  public function __construct()
  {
    $this->db_ax = Database::connect("ax");
    $this->db_live = Database::connect();
  }

  public function getShortShipInv($file)
  {
    try {
      preg_match('/(INV|PK)_DSC_([0-9]{4})_([0-9]+)/i', $file, $data);
      if ($data) {
        $arr = explode("_", $data[0]);
        if (count($arr) !== 4) {
          throw new \Exception("Error data incorrect.");
        }

        return [
          "result" => true,
          "data" => $arr
        ];
      } else {
        return [
          "result" => false,
          "data" => null
        ];
      }
    } catch (\Exception $e) {
      return [
        "result" => false,
        "data" => $e->getMessage()
      ];
    }
  }

  public function getShortShipSO($voucherSeries, $voucherNo)
  {
    $sql = "SELECT
		S.DSG_EDDDate, 
		S.DSG_CheckerCustomer,
		CJ.SALESID,
    S.DSG_CustomsBy
		FROM CUSTCONFIRMJOUR CJ
		LEFT JOIN SALESTABLE S ON S.SALESID = CJ.SALESID
		WHERE CJ.DSG_VOUCHERSERIES = ?
		AND CJ.DSG_VOUCHERNO = ?
		AND S.DSG_CheckerCustomer <> ''
		AND S.DSG_CheckerCustomer IS NOT NULL";

    try {
      // code
      $data = Database::rows(
        $this->db_ax,
        $sql,
        [
          $voucherSeries,
          $voucherNo
        ]
      );

      if (count($data) === 0) {
        throw new \Exception("ไม่พบข้อมูล");
      }

      return [
        "result" => true,
        "data" => $data[0]
      ];
    } catch (\Exception $e) {
      return [
        "result" => false,
        "data" => $e->getMessage()
      ];
    }
  }

  public function getEmail($checkCustoms, $testMode = false)
  {
    // EmailCategory 20 = SUNISA, 21 = NICHAPAR

    if ($checkCustoms === "NICHAPAR") {
      $emailCat = 21;
    } else if ($checkCustoms === "SUNISA") {
      $emailCat = 20;
    } else {
      $emailCat = 0;
    }

    $sql = "SELECT 
    E.Email, 
    E.EmailType, 
    EmailCategory
    FROM EmailLists E
    WHERE EmailCategory = $emailCat
    AND ProjectID = 39 
    AND [Status] = 1";

    $rows = Database::rows(
      $this->db_live,
      $sql
    );

  	
    
    $to = [];
    $cc = [];
    $sender = "";

    if ($testMode === true)
  	{
  		$to = ["wattana_r@deestone.com", "wiriya_y@deestone.com"];
  		$sender = "it_ea@deestone.com";
  	} else {
	 	foreach ($rows as $row) {
	      if ($row["EmailType"] === 1) {
	        $to[] = $row["Email"];
	      } else if ($row["EmailType"] === 2) {
	        $cc[] = $row["Email"];
	      } else if ($row["EmailType"] === 4) {
	        $sender = $row["Email"];
	      }
	    }
  	}

    return ["to" => $to, "cc" => $cc, "sender" => $sender];
  }

  public function getTrans($voucherSeries, $voucherNo)
  {
    $sql = "SELECT
      CCJ.SALESID,
      CCJ.DSG_VOUCHERTYPE AS VTYPE,
      CCJ.DSG_VOUCHERSERIES AS VSERIES,
      CCJ.DSG_VoucherNo AS VNO,
      CIT.DSG_ITEMNAMEENG AS SIZE,
      CCT.ITEMID,
      CCT.DSG_CUSTOM_QTY AS CUSTOM,
      CCT.DSG_CUSTOM_QTY - (
        CASE 
            WHEN CST.QTY IS NULL THEN 0
            ELSE CST.QTY
        END
      ) AS SHORT,
      (
        CASE 
            WHEN CST.QTY IS NULL THEN 0
            ELSE CST.QTY
        END
      ) AS SHIPPED
      FROM CUSTCONFIRMJOUR CCJ
      LEFT JOIN CUSTCONFIRMTRANS CCT 
      ON CCT.CONFIRMID = CCJ.CONFIRMID 
      AND CCT.DATAAREAID = 'dsc'
      LEFT JOIN (
      SELECT 
      BI.DSG_PICKITEMID,
      BI.DSG_MASTERITEMID,
      BI.DSG_ITEMNAMEENG,
      BI.PICKINGLISTID
      FROM DSG_CustomBisItem BI
      WHERE BI.DATAAREAID = 'dsc'
      GROUP BY 
      BI.DSG_PICKITEMID,
      BI.DSG_MASTERITEMID,
      BI.DSG_ITEMNAMEENG,
      BI.PICKINGLISTID
      ) CIT ON CIT.DSG_PICKITEMID = CCT.ITEMID AND CIT.PICKINGLISTID = CCJ.CONFIRMID 
      LEFT JOIN CUSTPACKINGSLIPTRANS CST 
      ON CST.SALESID = CCT.SALESID 
      AND CCT.ITEMID = CST.ITEMID 
      AND CST.DATAAREAID = 'dsc'
      AND CST.INVENTTRANSID = CCT.INVENTTRANSID
      AND CST.PACKINGSLIPID = (
        SELECT TOP 1 MAX(CSJ.PACKINGSLIPID) AS LATESTPACKINGSLIPID
        FROM CUSTPACKINGSLIPJOUR CSJ 
        WHERE CSJ.SALESID = CST.SALESID 
        AND CSJ.DATAAREAID = 'dsc'
      )
      WHERE CCJ.DSG_VOUCHERNO = ?
      AND CCJ.DSG_VOUCHERSERIES = ?
      AND CCT.DATAAREAID = 'dsc'
      AND CCJ.DATAAREAID = 'dsc'
      AND CCT.DSG_Custom_QTY - (
        CASE 
            WHEN CST.QTY IS NULL THEN 0
            ELSE CST.QTY
        END
      ) <> 0
      AND CCJ.DSG_NoYesId_Disabled <> 1
      GROUP BY 
      CCJ.SALESID,
      CCJ.DSG_VOUCHERTYPE,
      CCJ.DSG_VOUCHERSERIES,
      CCJ.DSG_VoucherNo,
      CIT.DSG_ITEMNAMEENG,
      CCT.ITEMID,
      CCT.DSG_CUSTOM_QTY,
      CST.QTY";

    $data = Database::rows(
      $this->db_ax,
      $sql,
      [
        $voucherNo,
        $voucherSeries
      ]
    );

    if (count($data) === 0) {
      return [];
    }

    return $data;
  }

  public function getSubject($voucherSeries, $voucherNo, $isNoShort, $testMode = false)
  {
  	$txtTest = "";
  	if ($testMode === true)
  	{
  		$txtTest = "[Test]";
  	}

    if ($isNoShort === true) {
      return $txtTest . " INV # DSC/" . $voucherSeries . "/" . $voucherNo . " : NO SHORT";
    } else {
      return $txtTest . " INV # DSC/" . $voucherSeries . "/" . $voucherNo . " : Short Ship";
    }
  }

  public function getBody($voucherSeries, $voucherNo, $loadingDate, $checkCustoms, $checkCustomsBy,  $trans, $noShort)
  {
    $text = "";
    $text .= "Dear All <br><br>";

    $text .= "Invoice No. : DSC/" . $voucherSeries . "/" . $voucherNo . "<br>";
    $text .= "Loading Date : " . date("d/m/Y", strtotime($loadingDate)) . "<br>";

    if ($noShort === true) {
      $text .= "<h2>NO SHORT</h2>";
    } else {
      $text .= "<br><table border='1' cellspacing='0' cellpadding='3'>
      <tr style='background: #eeeeee;'>
        <td style='text-align:center;'>SIZE</td>
        <td style='text-align:center;'>CUSTOM</td>
        <td style='text-align:center;'>SHORT</td>
        <td style='text-align:center;'>TOTAL SHIPPED</td>
      </tr>
    ";

      foreach ($trans as $t) {
        $text .= "<tr>";
        $text .= "<td>" . $t["SIZE"] . "</td>";
        $text .= "<td style='text-align: center;'>" . (int) $t["CUSTOM"] . "</td>";
        $text .= "<td style='text-align: center;'>" . (int) $t["SHORT"] . "</td>";
        $text .= "<td style='text-align: center;'>" . (int) $t["SHIPPED"] . "</td>";
        $text .= "</tr>";
      }

      $text .= "</table> <br>";
    }

    $text .= "Custom By " . $checkCustomsBy . "<br>";
    $text .= "Checker By " . $checkCustoms . "<br>";

    return $text;
  }

  public function getEmailFailed()
  {
    // tippawan_p@deestone.com
    return ["to" => ["tippawan_p@deestone.com"]];
  }
}
