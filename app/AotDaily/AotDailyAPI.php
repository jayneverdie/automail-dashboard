<?php

namespace App\AotDaily;

use App\Common\Database;
use App\Common\Automail;
use Webmozart\Assert\Assert;

class AotDailyAPI {

	private $db_ax = null;
	private $db_live = null;
	private $automail = null;

	public function __construct() {
		$this->db_ax = Database::connect('ax');
		$this->db_live = Database::connect();
		$this->automail = new Automail;
  }

	public function getsubjectBooking() {
		try {
			return 'Booking API ';
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}
	

	public function getMailCustomer($projectId) {
		try {

			$listsTo = [];
			$listsCC = [];
			$listsInternal = [];
			$listsSender = [];

			$query = Database::rows(
				$this->db_live,
				"SELECT * FROM EmailLists WHERE ProjectID=? AND Status=?",[$projectId,1]
			);

			foreach($query as $q) {
				if ($q['EmailType']==1 && $q['EmailCategory']==16) {
					$listsTo[] = $q['Email'];
				}else if($q['EmailType']==2 && $q['EmailCategory']==17){
					$listsCC[] = $q['Email'];
				}else if($q['EmailType']==1 && $q['EmailCategory']==17){
					$listsInternal[] = $q['Email'];
				}else if($q['EmailType']==4 && $q['EmailCategory']==17){
					$listsSender[] = $q['Email'];
				}
			}

			return [
				'to' => $listsTo,
				'cc' => $listsCC,
				'internal' => $listsInternal,
				'sender' => $listsSender
			];

		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}

	public function getBookingBody() {
			$text = '';
			
			$text .= 'Dear Sir / Madam <br><br>';
			$text .= 'Please see booking daily report as attached. These report is automatic generate by system.<br><br>';
			$text .= 'Best Regards, <br><br>';
			$text .= 'Ms. Acharapron I.<br>';
			$text .= 'Export Customer Service<br>';
			$text .= 'Tel: (+66 2) 420 0038 #576<br>';
			$text .= 'Fax: (+66 2) 420 0057<br>';

			return $text;
 	}

	public function getBookingSubject() {
		$date = date("Y-m-d H:i:s");
		$format_date = date("m/d/Y", strtotime($date));

		$text = 'AOT Booking Daily Report ' . $format_date;

		return $text;
	}






}
