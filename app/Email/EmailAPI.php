<?php

namespace App\Email;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Common\Database;
use App\Common\Message;
// use App\Email\EmailAPI;
use App\Logs\LogsAPI;

class EmailAPI
{
	public function __construct()
	{
		$this->db = Database::connect();
		$this->message = new Message;
		$this->logs = new LogsAPI;
	}

	public function all($filter)
	{
		return Database::rows(
			$this->db,
			"SELECT 
			ID,
			CustomerCode,
			Email
			FROM EmailMapping
			WHERE $filter"
		);
	}

	public function update($name, $pk, $value, $table)
	{
		$update = Database::query(
			$this->db,
			"UPDATE $table
	      SET $name = ?
	      WHERE ID = ?",
			[
				$value,
				$pk
			]
		);

		if ($update) {
			$this->logs->InsertLogs($name, $pk, $value, $table);
			return $this->message->result(true, 'Update successful!');
		} else {
			return $this->message->result(false, 'Update failed!');
		}
	}

	public function createLists($email_list, $email_type, $project)
	{
		$create = Database::query(
			$this->db,
			"INSERT INTO EmailLists(
	        Email,
					EmailType,
					ProjectID
	      ) VALUES(?, ?, ?)",
			[
				$email_list,
				$email_type,
				$project
			]
		);

		if ($create) {
			return $this->message->result(true, 'Create successful!');
		} else {
			return $this->message->result(false, 'Create failed!');
		}
	}

	public function DeleteLists($id)
	{
		$create = Database::query(
			$this->db,
			"DELETE FROM EmailLists
			WHERE ID=?",
			[
				$id
			]
		);

		if ($create) {
			return $this->message->result(true, 'Delete successful!');
		} else {
			return $this->message->result(false, 'Delete failed!');
		}
	}

	public function getEmailLists($filter)
	{
		return Database::rows(
			$this->db,
			"SELECT 
			E.ID,
			E.CustomerCode,
			E.Email,
			E.Port,
			ET.Description [EmailType],
			P.ProjectName,
			EC.Description [EmailCategory],
			S.status_name [Status],
			E.EmpCode_AX
			FROM EmailLists E
			LEFT JOIN EmailType ET ON ET.ID = E.EmailType
			LEFT JOIN Project P ON P.ProjectID = E.ProjectID
			LEFT JOIN EmailCategory EC ON EC.ID = E.EmailCategory
			LEFT JOIN web_status S ON S.id = E.Status
			WHERE $filter"
		);
	}

	public function getEmailCategory()
	{
		return Database::rows(
			$this->db,
			"SELECT * FROM EmailCategory"
		);
	}

	public function getEmailType()
	{
		return Database::rows(
			$this->db,
			"SELECT * FROM EmailType"
		);
	}

	public function getEmailProject()
	{
		return Database::rows(
			$this->db,
			"SELECT * FROM Project"
		);
	}

	public function deleteEmailMapping($id)
	{
		$delete = Database::query(
			$this->db,
			"DELETE FROM EmailMapping
			WHERE ID = ?",
			[
				$id
			]
		);

		if ($delete) {
			return $this->message->result(true, 'Delete successful!');
		} else {
			return $this->message->result(false, 'Delete failed!');
		}
	}

	public function createEmailMapping($customer, $email)
	{
		$create = Database::query(
			$this->db,
			"INSERT INTO EmailMapping(CustomerCode, Email)
			VALUES(?, ?)",
			[
				$customer,
				$email
			]
		);

		if ($create) {
			return $this->message->result(true, 'Create successful!');
		} else {
			return $this->message->result(false, 'Create failed!');
		}
	}

	public function sendEmail(
		$subject = '',
		$message = '',
		$to = [],
		$cc = [],
		$bcc = [],
		$file = [],
		$replyTo = '',
		$sender = '',
		$debug = 0
	) {

		$mail = new PHPMailer(true);

		try {
			$mail->isSMTP();
			$mail->SMTPDebug = $debug;
			$mail->Host = EMAIL_HOST;
			$mail->SMTPAuth = true;
			$mail->Username = EMAIL_USER;
			$mail->Password = EMAIL_PASS;
			$mail->SMTPSecure = 'ssl';
			$mail->SMTPOptions = [
				'ssl' => [
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true
				]
			];
			$mail->Port = EMAIL_PORT;

			$mail->From = $sender;
			$mail->FromName = $sender;
			$mail->Sender = EMAIL_USER;

			if (count($to) > 0) {
				foreach ($to as $_to) {
					$mail->addAddress($_to);
				}
			} else {
				throw new \Exception('No recipients mail.');
			}

			if (count($cc) > 0) {
				foreach ($cc as $_cc) {
					$mail->addCC($_cc);
				}
			}

			if (count($bcc) > 0) {
				foreach ($bcc as $_bcc) {
					$mail->addBCC($_bcc);
				}
			}

			if (count($file) > 0) {
				foreach ($file as $_file) {

					// preg_match_all('/.html|.htm/i', $_file, $checkHTML);
					// preg_match_all('/.pdf/i', $_file, $checkPDF);
					// preg_match_all('/.xlsx|/i', $_file, $checkExcel);

					// if (count($checkHTML[0]) > 0) {

					// 	$mail->addAttachment($_file, $_file . '.html');

					// } else if (count($checkPDF[0]) > 0) {

					// 	$mail->addAttachment($_file, $_file . '.pdf');

					// } else {

					$mail->addAttachment($_file);
					// }
				}
			}

			if ($replyTo !== '' && $replyTo !== null) {
				$mail->addReplyTo($replyTo);
			}

			$mail->isHTML(true);
			$mail->Subject = $subject;
			$mail->Body    = $message;
			$mail->CharSet = 'UTF-8';

			if ($mail->send()) {
				// return 'Message has been sent';
				return true;
			} else {
				throw new \Exception(str_replace('https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting', '', $mail->ErrorInfo));
			}
		} catch (\Exception $e) {
			return $e->getMessage();
			// throw new \Exception(str_replace('https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting', '', $mail->ErrorInfo));
		}
	}
}
