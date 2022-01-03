<?php

namespace App\CustomsInvoice_ByTruck;

use App\Common\Database;

class CustomsInvAPI
{
  private $db_ax = null;
  private $db_live = null;

  public function __construct()
  {
    $this->db_ax = Database::connect("ax");
    $this->db_live = Database::connect();
  }

  public function getCustInv($file)
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

  public function getCustSO($voucherSeries, $voucherNo)
  {
    $sql = "SELECT
		S.DSG_EDDDate, 
		S.DSG_TOPORTDESC,
		S.DSG_PRIMARYAGENTID,
		A.DSG_DESCRIPTION,
		S.DSG_CUSTOMSBY,
		S.DSG_CheckerCustomer,
		CJ.SALESID,
		S.SALESNAME
		FROM CUSTCONFIRMJOUR CJ
		LEFT JOIN SALESTABLE S ON S.SALESID = CJ.SALESID
		INNER JOIN DSG_AGENTTABLE A ON S.DSG_PRIMARYAGENTID = A.DSG_AGENTID 
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

  public function checkByTruck($salesId)
  {
    $sql = "SELECT *
    FROM SalesTable
    WHERE DlvMode = 'TRUCK' AND SALESID = ?";

    try {
      // code
      $data = Database::rows(
        $this->db_ax,
        $sql,
        [
          $salesId
        ]
      );

      if (count($data) === 0) {
        throw new \Exception("Sale Order นี้ Mode of delivery ไม่ใช่ By TRUCK");
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

  public function getEmail($checkCustoms)
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
    AND ProjectID = 40 
    AND [Status] = 1";

    $rows = Database::rows(
      $this->db_live,
      $sql
    );

    $to = [];
    $cc = [];
    $sender = "";

    foreach ($rows as $row) {
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

    return ["to" => $to, "cc" => $cc, "sender" => $sender];
  }

  public function getTrans($salesId)
  {
    $sql = "SELECT *
    FROM LOGS
    WHERE SO = ? 
    AND ProjectID = 40";

    $data = Database::rows(
      $this->db_live,
      $sql,
      [
        $salesId
      ]
    );
   
    if (count($data) === 0) {
      return [];
    }

    return $data;
  }

  public function getSubject($voucherSeries, $voucherNo, $CustomerName,$isNoRevised)
  {
    if ($isNoRevised === true) 
    {
      return "เดินพิธีการ : อินวอยซ์ # DSC/" . $voucherSeries . "/" . $voucherNo . "/". $CustomerName ." / BY TRUCK";
      // ' เดินพิธีการ : อินวอยซ์ # DSC/yyyy/xxx   / ชื่อลูกค้า (Table : Sale order >> Tab : General >> Field >>Name) / BY TRUCK
    } else {
      return "เดินพิธีการ : อินวอยซ์ # DSC/" . $voucherSeries . "/" . $voucherNo . "/". $CustomerName ." / BY TRUCK REVISED Agent,Tax ID";
    }
  }

  public function getBody($invno, $checkCustoms, $CustomsBy, $Toport, $agent, $loadingDate, $salesId ,$customer)
  {					
    $text = "";
    $text .= "Dear  Shipping Team <br><br>";
    $text .= "รบกวนเดินพิธีการส่งออก เอกสารตามไฟล์แนบ <br><br>";

    $text .= "Invoice No.	:	" . $invno . "<br>";
    $text .= "Loading Date.	:	" . date("d/m/Y", strtotime($loadingDate)) . "<br>";
    $text .= "To port 	:	" . $Toport . "<br>";
    $text .= "Agent & Tax ID  :	" . $agent . "<br>";
    $text .= "Custom by	:	" . $CustomsBy . "<br>";
    $text .= "Checker by	:	" . $checkCustoms . "<br>";
    

    return $text;
  }

  public function getEmailFailed()
  {
    // tippawan_p@deestone.com
    return ["to" => ["tippawan_p@deestone.com"]];
    // return ["to" => ["pattayanee_r@deestone.com","weerawat_y@deestone.com"]];
  }
}
