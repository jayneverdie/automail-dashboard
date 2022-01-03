<?php

namespace App\ShippingPlanCamso_Weekly;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class ShippingPlanCamsoAPI {

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
                else if($q['EmailType']==2 && $q['EmailCategory']==16)
                {
                    $listsCCExternal[] = $q['Email'];
                }
                else if($q['EmailType']==4 && $q['EmailCategory']==17)
                {
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

    public function getSubject() 
    {
		return 'Report :  (Private) Limited & Camso Loadstar (Private) Limited';;
	}
	public function getBody() {
			$text = '';
			$text .= 'Dear Sir / Madam <br><br>';
			$text .= 'Please see updated shipment report as attached file for your reference.  Thank you.<br><br><br>';
			$text .= 'Best Regards, <br><br>';
			$text .= 'Khanittha (Sai).<br>';
			// $text .= 'Export Customer Service<br>';
			// $text .= 'Tel: (+66 2) 420 0038 #576<br>';
			// $text .= 'Fax: (+66 2) 420 0057<br>';

			return $text;
 	}

	
}