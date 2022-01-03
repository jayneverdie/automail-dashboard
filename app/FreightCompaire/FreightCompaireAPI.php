<?php
namespace App\FreightCompaire;
use App\Common\Database;
use App\Common\CSRF;

class FreightCompaireAPI
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

    public function getLogApprove()
    {
        try 
        {
            $logapprove = Database::rows(
                $this->db_ax,
                "SELECT F.QUOTATIONID,F.SALESIDTXT AS SALESID
                ,CAST(ROUND(F.DSG_FREIGHTCHARGE,2)AS DECIMAL(18,2)) DSG_FREIGHTCHARGE
                ,F.SALESNAME,F.DSG_TOPORTDESC,F.DSG_COMPAIRE1,F.DSG_COMPAIRE2
                ,F.DSG_AGENT1,F.DSG_AGENT2
                ,A1.DSG_DESCRIPTION DSG_AGENTDESC1,A2.DSG_DESCRIPTION DSG_AGENTDESC2
                ,F.DSG_SHIPPINGLINE1,F.DSG_SHIPPINGLINE2
                ,CAST(ROUND(F.FEIGHTRATE1,2)AS DECIMAL(18,2)) FEIGHTRATE1
                ,CAST(ROUND(F.FEIGHTRATE2,2)AS DECIMAL(18,2)) FEIGHTRATE2
                ,CAST(ROUND(F.ENS_AMS1,2)AS DECIMAL(18,2)) ENS_AMS1
                ,CAST(ROUND(F.ENS_AMS2,2)AS DECIMAL(18,2)) ENS_AMS2
                ,CAST(ROUND(F.LSS_CHARGE1,2)AS DECIMAL(18,2)) LSS_CHARGE1
                ,CAST(ROUND(F.LSS_CHARGE2,2)AS DECIMAL(18,2)) LSS_CHARGE2
                ,CAST(ROUND(F.WARRISCKCHARGE1,2)AS DECIMAL(18,2)) WARRISCKCHARGE1
                ,CAST(ROUND(F.WARRISCKCHARGE2,2)AS DECIMAL(18,2)) WARRISCKCHARGE2
                ,CAST(ROUND(F.TOTALUSD1,2)AS DECIMAL(18,2)) TOTALUSD1
                ,CAST(ROUND(F.TOTALUSD2,2)AS DECIMAL(18,2)) TOTALUSD2
                ,F.REMARK1,F.REMARK2,F.CREATEDBY
                ,CASE 
                    WHEN F.DSG_CURRENCYCODE = 'EUB' 
                        THEN 'USD '+ CONVERT(NVARCHAR,CAST(ROUND(F.DSG_EXCHRATEUSB,4)AS DECIMAL(18,4))) 
                         + ' , EUB ' + CONVERT(NVARCHAR,CAST(ROUND(F.DSG_EXCHRATEEUB,4)AS DECIMAL(18,4)))
                    ELSE 'USD ' + CONVERT(NVARCHAR,CAST(ROUND(F.DSG_EXCHRATE,4)AS DECIMAL(18,4)))
                END AS 'EXCHANGERATES'
                ,F.DSG_VOLUME
                
                FROM DSG_FREIGHTPREPAID F 
                JOIN DSG_AGENTTABLE A1 ON F.DSG_AGENT1 = A1.DSG_AGENTID AND A1.DATAAREAID = 'DSC'
                JOIN DSG_AGENTTABLE A2 ON F.DSG_AGENT2 = A2.DSG_AGENTID AND A2.DATAAREAID = 'DSC'
                WHERE F.SENDAPPROVE = 1"
            );

            if ($logapprove) 
            {
                return $logapprove;
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

    public function getEmailSend($projectID,$CREATEDBY)
    {
        $query = Database::rows(
            $this->db_live,
            "SELECT 
            Email, 
            EmailType, 
            EmailCategory
            FROM EmailLists 
            WHERE ProjectID = ? 
            AND [Status] = 1 
            AND EmpCode_AX = ?
            AND EmailType = 4",[$projectID,$CREATEDBY]
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

        foreach ($rows as $row) 
        {
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
                $tocomplete[] = $row["Email"];
            }
        }

        return ["to" => $to, "cc" => $cc, "tocomplete" => $tocomplete];
    }

    public function getSubject($SO_NO,$CustName)
    {
        return "ขออนุมัติค่า Freight Compaire SO No: $SO_NO  $CustName";
    }

    public function getBody($PI_NO,$SO_NO,$FreightCharge,$CustName,$Port,
                            $Compaire1,$Compaire2,$Agent1,$Agent2,$ship1,$ship2,$rate1,
                            $rate2,$Ens1,$Ens2,$Lss1,$Lss2,$War1,$War2,
                            $TotalUSD1,$TotalUSD2,$Remark1,$Remark2,
                            $ApproveBy,$nonce,$Createby,$ExchangeRates,$Volume)
    {       
        $chooseuse="";
        if($Compaire1 =="1")
        {
            $compairetext1 = "<input type='checkbox' name='check1' onclick='return false;' checked> ";
            $chooseuse .= $Agent1;
        }
        else
        {
            $compairetext1 = "<input type='checkbox' name='check1' onclick='return false;' > ";
            $chooseuse .= "";
        }

        if($Compaire2 =="1")
        {
            $compairetext2 = "<input type='checkbox' name='check2' onclick='return false;' checked> ";
            $chooseuse .= $Agent2;
        }
        else
        {
            $compairetext2 = "<input type='checkbox' name='check2' onclick='return false;' > ";
            $chooseuse .= "";
        }
        //lungryn.deestonegrp.com:8910
        $url = 'http://lungryn.deestonegrp.com:8910/automail/freightcompaire/approvepage?PI_NO='.$PI_NO
                .'&SO_NO='.$SO_NO
                .'&chooseuse='.$chooseuse
                .'&nonce='.$nonce
                .'&Createby='.$Createby; 
       
        $text = "";
        $text .= "<h3><u>Freight Compaire</u></h3>";
        $text .= "<b>Freight Prepaid : </b><br>";
        //<input type='checkbox' name='check1' onclick='return false;'> 
        $text .= "<table width='80%'>
                    <tr>
                        <td width='25%'><b>PI No :  </b>" . $PI_NO . "</td>
                        <td width='25%'><b>SO No :  </b>" . $SO_NO . "</td>
                        <td width='20%'><b>Freight charge USD :  </b>" . $FreightCharge . "</td>
                        <td width='30%'><b>Exchange rates :  </b>" . $ExchangeRates . "</td>
                    </tr>
                    <tr>
                        <td colspan='4'><b>Customer name :  </b>" . $CustName . "</td>
                    </tr>
                    <tr>
                        <td colspan='4'>
                            <table width ='100%'>
                                <tr>
                                    <td width='35%'><b>Port :  </b>" . $Port . "</td>
                                    <td><b>Volume :  </b>" . $Volume . "</td>
                                <tr>
                            </table>
                        </td>
                        
                    </tr>
                    <tr>
                        <td colspan='4'>
                            <table width ='100%'>
                                <tr>
                                    <td width='35%'>
                                        <b>Compaire I :  </b>".$compairetext1." 
                                    </td>
                                    <td>
                                        <b>Compaire II :  </b>" . $compairetext2 . "
                                    </td>
                                <tr>
                            </table>
                        </td>
                        
                    </tr>
                    <tr>
                        <td colspan='4'>
                            <table width ='100%'>
                                <tr>
                                    <td width='35%'>
                                        <b>Agent :  </b>".$Agent1." 
                                    </td>
                                    <td>
                                        <b>Agent :  </b>" . $Agent2 . "
                                    </td>
                                <tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='4'>
                            <table width ='100%'>
                                <tr>
                                    <td width='35%'>
                                        <table>
                                            <tr>
                                                <td><b>Shipping Line :  </b></td>
                                                <td>" . $ship1 . "</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>Freight rate USD :  </b>".$rate1."</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>Ens/Ams :  </b>".$Ens1."</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>Lss :  </b>".$Lss1."</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>War risk :  </b>".$War1."</td>
                                            </tr>
                                            <tr>
                                                <td><b>Total USD :  </b></td>
                                                <td>" . $TotalUSD1 . " / " . $Volume . "</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td>
                                        <table>
                                            <tr>
                                                <td><b>Shipping Line :  </b></td>
                                                <td>" . $ship2 . "</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>Freight rate USD :  </b>".$rate2."</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>Ens/Ams :  </b>".$Ens2."</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>Lss :  </b>".$Lss2."</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>War risk :  </b>".$War2."</td>
                                            </tr>
                                            <tr>
                                                <td><b>Total USD :  </b></td>
                                                <td>" . $TotalUSD2 . " / " . $Volume . "</td>
                                            </tr>
                                        </table>
                                    </td>
                                <tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='4'><b>Remark :  </b>" . nl2br($Remark1) . "</td>
                    </tr>
                    <tr>
                        <td colspan='4'><b>Choose to Use :  </b>" . $chooseuse . "</td>
                    </tr>
                    <tr>
                        <td colspan='4'><b>Approved by :  </b>" . $ApproveBy . "</td>
                    </tr>
                    <tr>
                        <td colspan='4'><b>ลิ้งค์ยืนยันการอนุมัติ :   </b><a href='".$url ."'>คลิกที่นี่</a></td>
                    </tr>
                  </table>";
        return $text;
    }

    public function UpdateToAX_SendApprove1($status, $PI_NO, $Createby, $SO_NO)
    {
        try 
        {
            $logging = Database::query(
                $this->db_ax,
                "UPDATE DSG_FREIGHTPREPAID 
                    SET SENDAPPROVE = ? 
                    WHERE SENDAPPROVE = 1 AND 
                    QUOTATIONID = ? AND CREATEDBY = ?
                    AND SALESIDTXT = ?",
                [
                    $status,
                    $PI_NO,
                    $Createby,
                    $SO_NO
                ]
            );
        } 
        catch (\Exception $e) {
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
                $daynonce = date('Y-m-d H:i:s',strtotime($date . "+7 days"));

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

    public function UpdateToAX_SendApprove2($pi,$status,$Createby,$so)
    {
        try {
            return Database::rows(
                $this->db_ax,
                "UPDATE DSG_FREIGHTPREPAID 
                    SET SENDAPPROVE = ?
                WHERE QUOTATIONID = ?
                AND SENDAPPROVE = 2
                AND CREATEDBY = ?
                AND SALESIDTXT = ?",
                [
                    $status,
                    $pi,
                    $Createby,
                    $so
                ]
            );

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getSubject_Complete($so,$status,$CustName)
    {
        if($status == 3)
        {
            return "ขออนุมัติค่า Freight Compaire SO No: $so $CustName ผ่านการอนุมัติเรียบร้อยแล้ว";
        }
        else if($status == 0)
        {
            return "ขออนุมัติค่า Freight Compaire SO No: $so $CustName ไม่ผ่านการอนุมัติ";
        }
        
    }

    public function getBody_SendApprove2($PI_NO,$SO_NO,$FreightCharge,$CustName,$Port,
                            $Compaire1,$Compaire2,$Agent1,$Agent2,$ship1,$ship2,$rate1,
                            $rate2,$Ens1,$Ens2,$Lss1,$Lss2,$War1,$War2,
                            $TotalUSD1,$TotalUSD2,$Remark1,$Remark2,
                            $ApproveBy,$nonce,$Remark_Approve,$status,
                            $ExchangeRates,$Volume)
    {       
        $chooseuse="";
        if($Compaire1 =="1")
        {
            $compairetext1 = "<input type='checkbox' name='check1' onclick='return false;' checked> ";
            $chooseuse .= $Agent1;
        }
        else
        {
            $compairetext1 = "<input type='checkbox' name='check1' onclick='return false;' > ";
            $chooseuse .= "";
        }

        if($Compaire2 =="1")
        {
            $compairetext2 = "<input type='checkbox' name='check2' onclick='return false;' checked> ";
            $chooseuse .= $Agent2;
        }
        else
        {
            $compairetext2 = "<input type='checkbox' name='check2' onclick='return false;' > ";
            $chooseuse .= "";
        }

        if($status =="0")
        {
            $remarkapprove = "<b>Remark Reject :  </b> $Remark_Approve ";
        }
        else
        {
            $remarkapprove = "";
        }

        $text = "";
        $text .= "<h3><u>Freight Compaire</u></h3>";
        $text .= "<b>Freight Prepaid : </b><br>";
        //<input type='checkbox' name='check1' onclick='return false;'> 
        $text .= "<table width='80%'>
                    <tr>
                        <td width='25%'><b>PI No :  </b>" . $PI_NO . "</td>
                        <td width='25%'><b>SO No :  </b>" . $SO_NO . "</td>
                        <td width='20%'><b>Freight charge USD :  </b>" . $FreightCharge . "</td>
                        <td width='30%'><b>Exchange rates :  </b>" . $ExchangeRates . "</td>
                    </tr>
                    <tr>
                        <td colspan='4'><b>Customer name :  </b>" . $CustName . "</td>
                    </tr>
                    <tr>
                        <td colspan='4'>
                            <table width ='100%'>
                                <tr>
                                    <td width='35%'><b>Port :  </b>" . $Port . "</td>
                                    <td><b>Volume :  </b>" . $Volume . "</td>
                                <tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                       <td colspan='4'>
                            <table width ='100%'>
                                <tr>
                                    <td width='35%'>
                                        <b>Compaire I :  </b>".$compairetext1." 
                                    </td>
                                    <td>
                                        <b>Compaire II :  </b>" . $compairetext2 . "
                                    </td>
                                <tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='4'>
                            <table width ='100%'>
                                <tr>
                                    <td width='35%'>
                                        <b>Agent :  </b>".$Agent1." 
                                    </td>
                                    <td>
                                        <b>Agent :  </b>" . $Agent2 . "
                                    </td>
                                <tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                         <td colspan='4'>
                            <table width ='100%'>
                                <tr>
                                    <td width='35%'>
                                        <table>
                                            <tr>
                                                <td><b>Shipping Line :  </b></td>
                                                <td>" . $ship1 . "</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>Freight rate USD :  </b>".$rate1."</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>Ens/Ams :  </b>".$Ens1."</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>Lss :  </b>".$Lss1."</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>War risk :  </b>".$War1."</td>
                                            </tr>
                                            <tr>
                                                <td><b>Total USD :  </b></td>
                                                <td>" . $TotalUSD1 . " / " . $Volume . "</td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td>
                                        <table>
                                            <tr>
                                                <td><b>Shipping Line :  </b></td>
                                                <td>" . $ship2 . "</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>Freight rate USD :  </b>".$rate2."</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>Ens/Ams :  </b>".$Ens2."</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>Lss :  </b>".$Lss2."</td>
                                            </tr>
                                            <tr>
                                                <td></td>
                                                <td><b>War risk :  </b>".$War2."</td>
                                            </tr>
                                            <tr>
                                                <td><b>Total USD :  </b></td>
                                                <td>" . $TotalUSD2 . " / " . $Volume . "</td>
                                            </tr>
                                        </table>
                                    </td>
                                <tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td colspan='4'><b>Remark :  </b>" . $Remark1 . "</td>
                    </tr>
                    <tr>
                        <td colspan='4'><b>Choose to Use :  </b>" . $chooseuse . "</td>
                    </tr>
                    <tr>
                        <td colspan='4'><b>Approved by :  </b>" . $ApproveBy . "</td>
                    </tr>
                    <tr>
                        <td colspan='4'>$remarkapprove</td>
                    </tr>
                    
                  </table>";
        return $text;
    }

    public function getRemark($SO_NO,$PI_NO,$Createby)
    {
        $query = Database::rows(
            $this->db_ax,
            "SELECT *
            FROM DSG_FREIGHTPREPAID
            WHERE SALESIDTXT = ? 
            AND QUOTATIONID = ?
            AND CREATEDBY = ?
            AND SENDAPPROVE = 2",[$SO_NO,$PI_NO,$Createby]
        );

        $remark1 = $query[0]['REMARK1'];
        $remark2 = $query[0]['REMARK2'];

        return ["remark1" => $remark1,"remark2" => $remark2];
    }

    public function getLogData($SO_NO,$PI_NO,$Createby)
    {
        $query = Database::rows(
            $this->db_ax,
            "SELECT F.QUOTATIONID,F.SALESIDTXT AS SALESID
            ,CAST(ROUND(F.DSG_FREIGHTCHARGE,2)AS DECIMAL(18,2)) DSG_FREIGHTCHARGE
            ,F.SALESNAME,F.DSG_TOPORTDESC,F.DSG_COMPAIRE1,F.DSG_COMPAIRE2
            ,F.DSG_AGENT1,F.DSG_AGENT2
            ,A1.DSG_DESCRIPTION DSG_AGENTDESC1,A2.DSG_DESCRIPTION DSG_AGENTDESC2
            ,F.DSG_SHIPPINGLINE1,F.DSG_SHIPPINGLINE2
            ,CAST(ROUND(F.FEIGHTRATE1,2)AS DECIMAL(18,2)) FEIGHTRATE1
            ,CAST(ROUND(F.FEIGHTRATE2,2)AS DECIMAL(18,2)) FEIGHTRATE2
            ,CAST(ROUND(F.ENS_AMS1,2)AS DECIMAL(18,2)) ENS_AMS1
            ,CAST(ROUND(F.ENS_AMS2,2)AS DECIMAL(18,2)) ENS_AMS2
            ,CAST(ROUND(F.LSS_CHARGE1,2)AS DECIMAL(18,2)) LSS_CHARGE1
            ,CAST(ROUND(F.LSS_CHARGE2,2)AS DECIMAL(18,2)) LSS_CHARGE2
            ,CAST(ROUND(F.WARRISCKCHARGE1,2)AS DECIMAL(18,2)) WARRISCKCHARGE1
            ,CAST(ROUND(F.WARRISCKCHARGE2,2)AS DECIMAL(18,2)) WARRISCKCHARGE2
            ,CAST(ROUND(F.TOTALUSD1,2)AS DECIMAL(18,2)) TOTALUSD1
            ,CAST(ROUND(F.TOTALUSD2,2)AS DECIMAL(18,2)) TOTALUSD2
            ,F.REMARK1,F.REMARK2,F.CREATEDBY
            ,F.REMARK1,F.REMARK2,F.CREATEDBY
            ,CASE 
                    WHEN F.DSG_CURRENCYCODE = 'EUB' 
                        THEN 'USD '+ CONVERT(NVARCHAR,CAST(ROUND(F.DSG_EXCHRATEUSB,4)AS DECIMAL(18,4))) 
                         + ' , EUB ' + CONVERT(NVARCHAR,CAST(ROUND(F.DSG_EXCHRATEEUB,4)AS DECIMAL(18,4)))
                    ELSE 'USD ' + CONVERT(NVARCHAR,CAST(ROUND(F.DSG_EXCHRATE,4)AS DECIMAL(18,4)))
                END AS 'EXCHANGERATES'
            ,F.DSG_VOLUME
            FROM DSG_FREIGHTPREPAID F 
            JOIN DSG_AGENTTABLE A1 ON F.DSG_AGENT1 = A1.DSG_AGENTID AND A1.DATAAREAID = 'DSC'
            JOIN DSG_AGENTTABLE A2 ON F.DSG_AGENT2 = A2.DSG_AGENTID AND A2.DATAAREAID = 'DSC'
            WHERE F.SALESIDTXT = ? 
            AND F.QUOTATIONID = ?
            AND F.CREATEDBY = ?
            AND F.SENDAPPROVE = 2",[$SO_NO,$PI_NO,$Createby]
        );

        if(count($query)>0)
        {
            $FreightCharge = $query[0]['DSG_FREIGHTCHARGE'];
            $CustName = $query[0]['SALESNAME'];
            $Port = $query[0]['DSG_TOPORTDESC'];
            $Compaire1 = $query[0]['DSG_COMPAIRE1'];
            $Compaire2 = $query[0]['DSG_COMPAIRE2'];
            $Agent1 = $query[0]['DSG_AGENTDESC1'];
            $Agent2 = $query[0]['DSG_AGENTDESC2'];
            $ship1 = $query[0]['DSG_SHIPPINGLINE1'];
            $ship2 = $query[0]['DSG_SHIPPINGLINE2'];
            $rate1 = $query[0]['FEIGHTRATE1'];
            $rate2 = $query[0]['FEIGHTRATE2'];
            $Ens1 = $query[0]['ENS_AMS1'];
            $Ens2 = $query[0]['ENS_AMS2'];
            $Lss1 = $query[0]['LSS_CHARGE1'];
            $Lss2 = $query[0]['LSS_CHARGE2'];
            $War1 = $query[0]['WARRISCKCHARGE1'];
            $War2 = $query[0]['WARRISCKCHARGE2'];
            $TotalUSD1 = $query[0]['TOTALUSD1'];
            $TotalUSD2 = $query[0]['TOTALUSD2'];
            $Remark1= $query[0]['REMARK1'];
            $Remark2 = $query[0]['REMARK2'];
            $CREATEDBY = $query[0]['CREATEDBY'];         
            $ExchangeRates = $query[0]['EXCHANGERATES'];
            $Volume = $query[0]['DSG_VOLUME'];

            return  [   "FreightCharge" => $FreightCharge,
                    "CustName" => $CustName,
                    "Port" => $Port,
                    "Compaire1" => $Compaire1,
                    "Compaire2" => $Compaire2,
                    "Agent1" => $Agent1,
                    "Agent2" => $Agent2,
                    "ship1" => $ship1,
                    "ship2" => $ship2,
                    "rate1" => $rate1,
                    "rate2" => $rate2,
                    "Ens1" => $Ens1,
                    "Ens2" => $Ens2,
                    "Lss1" => $Lss1,
                    "Lss2" => $Lss2,
                    "War1" => $War1,
                    "War2" => $War2,
                    "TotalUSD1" => $TotalUSD1,
                    "TotalUSD2" => $TotalUSD2,
                    "Remark1" => $Remark1,
                    "Remark2" => $Remark2,
                    "CREATEDBY" => $CREATEDBY,
                    "ExchangeRates" => $ExchangeRates,
                    "Volume" => $Volume
                ];
        }
        
    }


    public function getApproveBy()
    {
        return "รัตนา จันทร์บำรุง";
    }
}