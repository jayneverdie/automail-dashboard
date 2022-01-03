<?php

namespace App\Trucking_Agen;

use App\Common\Database;
use App\Common\CSRF;
use DateTime;


class TruckingAPI
{

    private $db_ax = null;
    private $db_live = null;
    private $csrf = null;

    public function __construct()
    {
        $this->db_ax = Database::connect("ax");
        $this->db_live = Database::connect();
        $this->csrf = new CSRF;
    }


    public function getTrucking($type)
    {
        try {

            $date = date("Y-m-d");

            $logapprove = Database::rows(
                $this->db_ax,
                "SELECT 
                
                S.DSG_TRUCKINGAGENT
                ,'DSC'+'/'+ CONVERT(varchar,C.DSG_VoucherSeries,101) + '/' + CONVERT(varchar,C.DSG_VoucherNo,101) AS  VOUCHER_NO
                ,CONVERT (varchar(10), S.DSG_RTN, 103) AS DSG_RTN
                ,CONVERT (varchar(10), S.DSG_EDDDATE, 103) AS DSG_EDDDATE
                ,S.DSG_SHIPPINGLINEDESCRIPTION
                ,S.DSG_BOOKINGNUMBER
                ,CONVERT(varchar,CONVERT (varchar(10),S.DSG_CLOSINGDATE, 103),101) AS ClosingDateTime
                , CONVERT(varchar, (S.DSG_ClosingTime / 86400))+ ':' + CONVERT(varchar, DATEADD(ss, S.DSG_ClosingTime, 0), 108) AS DSG_ClosingTime
                ,CONVERT (varchar(10), S.DSG_ETDDATE, 103) AS DSG_ETDDATE
                ,S.CUSTOMERREF
                ,'DSC-'+ S.QUOTATIONID AS QUOTATIONID
                ,'DSC-'+S.SALESID AS SALESID
                ,CAST(S.DSG_CONTAINER1X20 AS int) AS DSG_CONTAINER1X20
                ,CAST(S.DSG_CONTAINER1X40 AS int) AS DSG_CONTAINER1X40
                ,CAST(S.DSG_CONTAINER1X40HC AS int)AS DSG_CONTAINER1X40HC
                ,CAST(S.DSG_CONTAINER1X45HC AS int)AS DSG_CONTAINER1X45HC
                ,D.DSG_DESCRIPTION
                
                FROM SALESTABLE S
                LEFT JOIN DSG_AGENTTABLE D ON D.DSG_AGENTID = DSG_PRIMARYAGENTID AND D.DATAAREAID = 'dsc'
                LEFT JOIN CustConfirmJour C ON C.SALESID = S.SALESID 
               AND C.CONFIRMID = (SELECT  max(CONFIRMID) AS CONFIRMID FROM CustConfirmJour where SALESID = S.SALESID  AND DATAAREAID = 'dsc')
                
                WHERE convert(date,S.DSG_RTN) = ?
                AND C.DSG_VoucherSeries <> 0
                AND C.DSG_VoucherNo <> 0
                AND S.DSG_TRUCKINGAGENT = ?
                ORDER BY S.SALESID asc",
                [$date, $type]

            );

            if ($logapprove) {
                return $logapprove;
            } else {
                return [];
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getEmailSend($projectID)
    {
        $query = Database::rows(
            $this->db_live,
            "  SELECT 
            Email, 
            EmailType, 
            EmailCategory
            FROM EmailLists 
            WHERE ProjectID = ?
            AND [Status] = 1 
			AND EmailType = 4",
            [$projectID,]
        );

        $sendby = $query[0]['Email'];

        return ["sendby" => $sendby];
    }
    public function getEmail($projectID)
    {

        $sql = "SELECT 
        Email, 
        EmailType, 
        EmailCategory
        FROM EmailLists 
        WHERE ProjectID = $projectID 
        AND [Status] = 1";

        $rows = Database::rows(
            $this->db_live,
            $sql
        );

        $to = [];
        $cc = [];
        $tocomplete = [];

        foreach ($rows as $row) {
            if ($row["EmailType"] === 1) {
                $to[] = $row["Email"];
            } else if ($row["EmailType"] === 2) {
                $cc[] = $row["Email"];
            } else if ($row["EmailType"] === 4) {
                $tocomplete[] = $row["Email"];
            }
        }

        return ["to" => $to, "cc" => $cc, "tocomplete" => $tocomplete];
    }

    public function getSubject()
    {
        $date = date("d/m/Y");
        return "ตรวจสอบสถานะการคืนตู้ประจำวันที่ " . $date;
    }

    public function getBody(

        $getApprove
    ) {
        $chooseuse = "";
        $dateToppic = date("d/m/Y");



        $text = "";
        // $text .= "<h3><u>Freight Compaire</u></h3>";
        $text .= "เรียนผู้ให้บริการหัวลาก";
        $text .= "<BR><BR>";
        $text .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;เนื่องจากมีการฝากตู้ไว้ที่ลานผู้ให้บริการหัวลาก ดังนั้นเพื่อป้องกันปัญหาการคืนตู้ไม่ทันตามเวลาที่สายเรือกำหนด ทางบริษัทจึงทำรายงานสรุปตู้ที่ต้องคืนตู้ประจำวันที่  " . $dateToppic;
        $text .= "<BR><BR>";
        $text .= "ตามรายละเอียดด้านล่าง รบกวนตรวจสอบสถานะการคืนตู้ พร้อมแจ้งอีเมล์อัพเดทสถานะตู้ว่าคืนตู้ครบตามเวลาที่กำหนดหรือไม่ค่ะ";
        $text .= "<BR><BR>";
        //<input type='checkbox' name='check1' onclick='return false;'> 
        $text .= "<table border = '1'>
                    <tr bgcolor='#99CCFF'>
                    <td style='text-align:center'><font size='2px'><b>TRUCKING</font></font></b></td>
                    <td style='text-align:center'><font size='2px'><b>INVOICE NO.</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>RTN</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>LOADING DATE</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>CARRIER</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>BOOKINGNUMBER</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>CLOSING DATE&TIME</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>ETD DATE</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>PO. NO.</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>P/I NO.</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>SO NO.</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>20' FCL</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>40' FCL</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>40' HC</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>45' HC</font></b></td>
                    <td style='text-align:center'><font size='2px'><b>AGENT</font></b></td>
                       
                    </tr>";
        foreach ($getApprove as $log) {

            $str = $log["DSG_ClosingTime"];
            $Timset = explode(":", $str);
            $TimeClosing = $Timset[1] . "." . $Timset[2];

            $text .= "<tr>
                         <td ><font size='2px'>" . $log["DSG_TRUCKINGAGENT"] . "</font></td>
                         <td><font size='2px'>" . $log["VOUCHER_NO"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_RTN"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_EDDDATE"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_SHIPPINGLINEDESCRIPTION"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_BOOKINGNUMBER"] . "</font></td>
                         <td><font size='2px'>" . $log["ClosingDateTime"] . " " . $TimeClosing . "</font></td>
                         <td><font size='2px'>" . $log["DSG_ETDDATE"] . "</font></td>
                         <td><font size='2px'>" . $log["CUSTOMERREF"] . "</font></td>
                         <td><font size='2px'>" . $log["QUOTATIONID"] . "</font></td>
                         <td><font size='2px'>" . $log["SALESID"] . "</font></td>
                        <td><font size='2px'>" . $log["DSG_CONTAINER1X20"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_CONTAINER1X40"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_CONTAINER1X40HC"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_CONTAINER1X45HC"] . "</font></td>
                         <td><font size='2px'>" . $log["DSG_DESCRIPTION"] . "</font></td>
                         
                         </tr>";
        }

        $text .=  "</table>";

        // $signature = self::getsignatureTrucking($customer);

        $text .= "<tr><td colspan=2> <br><br><font size='2px' color='#696969'>Best regards, </font> </td></tr><BR><BR>";
        $text .= "<tr><td colspan=2><font size='5px' color='##1344C9' style='font-family:Brush Script MT;'  >Rattana Chanbamrung</font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#696969'>Global Customer Services Manager</font> </td></tr><BR><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#696969'><a href='www.deestone.com'>www.deestone.com</a></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#696969'>Tel: (+66 2) 420 0038 Ext. 506</font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#696969'>Fax: (+66 2) 420 5680</font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#696969'>Mob: (+66 8) 1378 4384</font> </td></tr>";



        return $text;
    }
}
