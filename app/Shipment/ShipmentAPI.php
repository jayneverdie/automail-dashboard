<?php

namespace App\Shipment;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class ShipmentAPI {

	private $db_live = null;
	private $automail = null;

	public function __construct() {
		$this->db_live = Database::connect();
		$this->automail = new Automail;
    }

    public function getMail($projectId) {
        try 
        {
            $listsToExternal = [];
            $listsCCExternal = [];
            $listsToInternal = [];
            $sendby = "";

            $query = Database::rows(
                $this->db_live,
                "SELECT * FROM EmailLists WHERE ProjectID=? AND Status=?",[$projectId,1]
            );

            foreach($query as $q) 
            {
                if ($q['EmailType']==1 && $q['EmailCategory']==16) 
                {
                    $listsToExternal[] = $q['Email'];
                }
                else if($q['EmailType']==1 && $q['EmailCategory']==17)
                {
                    $listsToInternal[] = $q['Email'];
                }
                else if($q['EmailType']==4 && $q['EmailCategory']==17)
                {
                    $sendby = $q['Email'];
                }
            }

            return [
                'toExternal' => $listsToExternal,
                'toInternal' => $listsToInternal,
                'sender' => $sendby
            ];

        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getSubject($date) 
    {
		return 'Test Daily Report API on '.$date;
	}
	public function getBody() {
			$text = '';
			$text .= 'Dear Carolin, <br><br>';
			$text .= 'Please find daily report in attachment for your reference.<br><br><br><br><br>';
			$text .= '<b>Best Regards, </b><br><br>';
			$text .= 'Nutcha C.<br>';
            $text .= 'Export Customer Services<br>';
            $text .= 'Office : +66 2420 0038 Ext#130<br>';
            $text .= 'Deestone Corporation Limited<br>';

			return $text;
 	}

	
}