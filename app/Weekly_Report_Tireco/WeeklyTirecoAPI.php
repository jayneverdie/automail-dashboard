<?php

namespace App\Weekly_Report_Tireco;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class WeeklyTirecoAPI
{

    private $db_live = null;
    private $automail = null;

    public function __construct()
    {
        $this->db_live = Database::connect();
        $this->automail = new Automail;
    }

    public function getMail($projectId)
    {
        try {
            $listsToExternal = [];
            $listsCCExternal = [];
            $listsToInternal = [];
            $sendby = "";

            $query = Database::rows(
                $this->db_live,
                "SELECT * FROM EmailLists WHERE ProjectID=? AND Status=?",
                [$projectId, 1]
            );

            foreach ($query as $q) {
                if ($q['EmailType'] == 1 && $q['EmailCategory'] == 16) {
                    $listsToExternal[] = $q['Email'];
                } else if ($q['EmailType'] == 1 && $q['EmailCategory'] == 17) {
                    $listsToInternal[] = $q['Email'];
                } else if ($q['EmailType'] == 2 && $q['EmailCategory'] == 16) {
                    $listsCCExternal[] = $q['Email'];
                } else if ($q['EmailType'] == 4 && $q['EmailCategory'] == 17) {
                    $sendby = $q['Email'];
                }
            }

            return [
                'toExternal' => $listsToExternal,
                'ccExternal' => $listsCCExternal,
                'toInternal' => $listsToInternal,
                'sender' => $sendby
            ];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getSubject($datecheck)
    {
        return 'Weekly Report : Greenball Corp Update ' . $datecheck;;
    }
    public function getBody()
    {
        $text = '';
        $text .= 'Dear Kenny,<br><br>';
        $text .= 'Please find attached the weekly report for your reference.<br><br><br>';

        $text .= "<tr><td colspan=2> <br><br><font size='4px' color='#030303'><b>Best regards,</b> </font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='5px' color='##1344C9' style='font-family:Brush Script MT;'  >Rattana Chanbamrung</font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Nutch C.</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Export Customer Services</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Office: +66 2420 0038 Ext#130</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Deestone Corporation Limited</b></font> </td></tr><BR>";

        return $text;
    }
    public function getSubjectTireco($datecheck)
    {
        return '(TEST) DS '.$datecheck.' Daily Email : Tireco weekly report';
    }
    public function getBodyTireco()
    {
        $text = '';
        $text .= 'Dear Lee,<br><br>';
        $text .= 'Please find attached the weekly report for your reference.<br><br><br>';

        $text .= "<tr><td colspan=2> <br><br><font size='4px' color='#030303'><b>Best regards,</b> </font> </td></tr><BR>";
        // $text .= "<tr><td colspan=2><font size='5px' color='##1344C9' style='font-family:Brush Script MT;'  >Rattana Chanbamrung</font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Nutch C.</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Export Customer Services</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Office: +66 2420 0038 Ext#130</b></font> </td></tr><BR>";
        $text .= "<tr><td colspan=2><font size='2px' color='#030303'><b>Deestone Corporation Limited</b></font> </td></tr><BR>";

        return $text;
    }
}
