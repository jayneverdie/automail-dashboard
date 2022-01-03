<?php

namespace App\Port;

use App\Port\PortAPI;
// use App\Email\EmailAPI;
use App\Common\View;
use App\Port\PortTable;
use App\Common\Datatables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PortController
{
	public function __construct()
	{
		$this->port = new PortAPI;
		// $this->email = new EmailAPI;
		$this->view = new View;
		$this->port_table = new PortTable;
		$this->datatables = new Datatables;
	}

	public function index($request, $response, $args)
	{
		return $this->view->render('pages/master/port');
	}

	public function all($request, $response, $args)
	{

		$parsedBody = $request->getParsedBody();

		$data = $this->port->all($this->datatables->filter($parsedBody));
		$pack = $this->datatables->get($data, $parsedBody);

		return $response->withJson($pack);
	}

	public function portType($request, $response, $args)
	{
		$result = $this->port->portType($args['customerport']);
		return $response->withJson($result);
	}

	public function portEmail($request, $response, $args)
	{
		$parsedBody = $request->getParsedBody();
		$port = $parsedBody['port'];
		$data = $this->port->portEmail($this->datatables->filter($parsedBody), $port);
		$pack = $this->datatables->get($data, $parsedBody);

		return $response->withJson($pack);
	}

	public function getEmailCategory($request, $response, $args)
	{
		$result = $this->port->getEmailCategory($args['project_id']);
		return $response->withJson($result);
	}

	public function create($request, $response, $args)
	{
		$parsedBody = $request->getParsedBody();

		if (trim($parsedBody['email_name']) === '') {
			return $response->withJson($this->message->result(false, 'Data must not be blank!'));
		}

		$result = $this->port->create(
			$parsedBody['email_name'],
			$parsedBody['email_type'],
			$parsedBody['email_category'],
			$parsedBody['project_id'],
			$parsedBody['port_name']
		);

		return $response->withJson($result);
	}

	public function delete($request, $response, $args)
	{
		$parsedBody = $request->getParsedBody();

		$result = $this->port->delete(
			$parsedBody['id']
		);

		return $response->withJson($result);
	}

	public function update($request, $response, $args)
	{
		$parsedBody = $request->getParsedBody();

		$result = $this->port->update(
			$this->port_table->field[$parsedBody['name']],
			$parsedBody['pk'],
			$parsedBody['value'],
			$this->port_table->table
		);

		return $response->withJson($result);
	}

	public function portUpload($request, $response, $args)
	{
		return $this->view->render('pages/port/port_upload');
	}

	public function upload($request, $response, $args)
	{
		$targetFile = 'files/port/import/' . \htmlspecialchars($_FILES["port_files"]["name"]);
		$fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
		$parsedBody = $request->getParsedBody();

		header('Content-Type: text/html; charset=utf-8');

		if ($_FILES["port_files"]["name"] === "") {
			echo "ไม่พบไฟล์. <a href='/port_upload'>ย้อนกลับ</a>";
			exit;
		}

		if ($_FILES["port_files"]["size"] > 500000) {
			echo "ไฟล์ใหญ่เกินไป. <a href='/port_upload'>ย้อนกลับ</a>";
			exit;
		}

		if ($fileType !== "ods" && $fileType !== "xlsx" && $fileType !== "xls") {
			echo "เฉพาะไฟล์นามสกุล ods, xlsx และ xls เท่านั้น. <a href='/port_upload'>ย้อนกลับ</a>";
			exit;
		}

		try {
			$move_file = move_uploaded_file($_FILES["port_files"]["tmp_name"], $targetFile);

			if ($move_file === false) {
				throw new \Exception("Upload ไม่สำเร็จ");
			}

			if (!isset($parsedBody['project']) && $parsedBody['project'] === "") {
				throw new \Exception("กรุณาเลือก project");
			}

			$spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($targetFile);
			$worksheet = $spreadsheet->getActiveSheet();
			$rows = $worksheet->toArray();

			// echo "<pre>".print_r($rows,true)."</pre>";
			// exit();

			if (count($rows) < 1) {
				throw new \Exception("ข้อมูลไม่ถูกต้อง");
			}

			$emailCategory[16] = [
				"ex" => 16,
				"in" => 17,
				"in2" => 18
			];

			$deleteOld = $this->port->updateOldPort($parsedBody['project']);

			$x = 0;
			foreach ($rows as $row) {

				if (count($row) < 5) {
					throw new \Exception("File format ไม่ถูกต้อง");
				}
				if ($x > 0) {
					$to = self::getPortEmail($row[2]); // to
					$cc = self::getPortEmail($row[3]); // cc
					$internal = self::getPortEmail($row[4]);
					$internal2 = self::getPortEmail($row[5]);

					// add to
					for ($i = 0; $i < count($to); $i++) {
						$this->port->addPort(
							trim($row[0]), // country
							trim($row[1]), // port
							trim($to[$i]), // to
							1, // type to
							(int) $parsedBody['project'], // portject id
							(int) $emailCategory[16]["ex"], // email cat external
							1
						);
					}

					// add cc
					for ($i = 0; $i < count($cc); $i++) {
						$this->port->addPort(
							trim($row[0]), // country
							trim($row[1]), // port
							trim($cc[$i]), // cc
							2, // type cc
							(int) $parsedBody['project'], // portject id
							(int) $emailCategory[16]["ex"], // email cat external
							1
						);
					}

					// add cc
					for ($i = 0; $i < count($internal); $i++) {
						$this->port->addPort(
							trim($row[0]), // country
							trim($row[1]), // port
							trim($internal[$i]), // internal
							1, // type to
							(int) $parsedBody['project'], // portject id
							(int) $emailCategory[16]["in"], // email cat internal
							1
						);
					}

					// add cc
					for ($i = 0; $i < count($internal2); $i++) {
						$this->port->addPort(
							trim($row[0]), // country
							trim($row[1]), // port
							trim($internal2[$i]), // internal
							1, // type to
							(int) $parsedBody['project'], // portject id
							(int) $emailCategory[16]["in2"], // email cat internal
							1
						);
					}
					// end loop
				}
				$x++;
			}

			echo "Upload success. <a href='/port_upload'>ย้อนกลับ</a>";
		} catch (\Exception $e) {
			echo "Error : " . $e->getMessage() . " <a href='/port_upload'>ย้อนกลับ</a>";
		}
	}

	public function project($request, $response, $args)
	{
		try {
			// $result = $this->port->getEmailCategory($args['project_id']);
			return $response->withJson($this->port->getProject());
		} catch (\Exception $e) {
			return [];
		}
	}

	public function getPortEmail($mailList)
	{
		try {
			if (trim($mailList) === '') {
				return [];
			}

			$email = explode('; ', trim($mailList, '; '));
			return $email;
		} catch (\Exception $e) {
			return [];
		}
	}

	public function export($request, $response, $args)
	{
		try {
			$parsedBody = $request->getParsedBody();

			$rows = $this->port->getCurrentEmail($parsedBody['project_export']);

			// echo "<pre>" . print_r($rows, true) . "</pre>";
			// exit;

			$emailCategory[16] = [
				"ex" => 16,
				"in" => 17,
				"in2" => 18
			];
			$country = "";
			$port = "";
			$portList = [];

			foreach ($rows as $p) {

				if ($country !== $p['Country'] && $p['Country'] != 'USA') {
					$portList[] = [
						"country" => $p['Country'],
						"port" => $p['Port'],
						"to" => self::getDataEMail($rows, $p['Country'], $p['Port'], 'To', $emailCategory[16]["ex"]),
						"cc" => self::getDataEMail($rows, $p['Country'], $p['Port'], 'Cc', $emailCategory[16]["ex"]),
						"internal" => self::getDataEMail($rows, $p['Country'], $p['Port'], 'To', $emailCategory[16]["in"]),
						"internal2" => self::getDataEMail($rows, $p['Country'], $p['Port'], 'To', $emailCategory[16]["in2"])
					];
				}

				$country = $p['Country'];

				if ($p['Country'] === '') {
					if ($port !== $p['Port']) {
						$portList[] = [
							"country" => $p['Country'],
							"port" => $p['Port'],
							"to" => self::getDataEMail($rows, $p['Country'], $p['Port'], 'To', $emailCategory[16]["ex"]),
							"cc" => self::getDataEMail($rows, $p['Country'], $p['Port'], 'Cc', $emailCategory[16]["ex"]),
							"internal" => self::getDataEMail($rows, $p['Country'], $p['Port'], 'To', $emailCategory[16]["in"]),
							"internal2" => self::getDataEMail($rows, $p['Country'], $p['Port'], 'To', $emailCategory[16]["in2"])
						];
					}
					$port = $p['Port'];
				}
			}

			foreach ($rows as $pl) {
				if ($pl['Country'] === "USA") {
					if ($port != $pl['Port']) {
						$portList[] = [
							"country" => $pl['Country'],
							"port" => $pl['Port'],
							"to" => self::getDataEMail($rows, $pl['Country'], $pl['Port'], 'To', $emailCategory[16]["ex"]),
							"cc" => self::getDataEMail($rows, $pl['Country'], $pl['Port'], 'Cc', $emailCategory[16]["ex"]),
							"internal" => self::getDataEMail($rows, $pl['Country'], $pl['Port'], 'To', $emailCategory[16]["in"]),
							"internal2" => self::getDataEMail($rows, $pl['Country'], $pl['Port'], 'To', $emailCategory[16]["in2"])
						];
					}
					$port = $pl['Port'];
				}
			}

			// echo "<pre>".print_r($portList,true)."<pre>";
			// exit();

			$data = $portList;

			if (count($data) === 0) {
				// throw new Exception("ไม่พบข้อมูล");
				echo "Error : ไม่พบข้อมูล" . " <a href='/port_upload'>ย้อนกลับ</a>";
				exit();
			}

			$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();

			$spreadsheet->getActiveSheet()
				->setCellValue('A1', 'COUNTRY')
				->setCellValue('B1', 'PORT')
				->setCellValue('C1', 'TO')
				->setCellValue('D1', 'CC')
				->setCellValue('E1', 'INTERNAL')
				->setCellValue('F1', 'INTERNAL2');

			$spreadsheet->getActiveSheet()->fromArray($data, null, 'A2');

			foreach (range('A', 'F') as $column) {
				$spreadsheet->getActiveSheet()->getColumnDimension($column)->setAutoSize(true);
			}

			$writer = new Xlsx($spreadsheet);

			$filename = [
				"7" => "camso_c1441",
				"8" => "camso_c2536",
				"9" => "camso_isf",
				"33" => "tiregroup"
			];

			$root = 'files/port/export/' . $filename[$parsedBody['project_export']] . ".xlsx";
			$writer->save($root);

			echo "<style>";
			echo "table {width: 100%; border-collapse: collapse;}";
			echo "td, tr, th {border: 1px solid #000000; padding: 5px; font-family:'Cordia New'; font-size:18px;}";
			echo "a {font-family:'Cordia New'; font-size:20px;}";
			echo "</style>";

			echo '<a href=' . '/' . trim($root) . '>Download Excel</a>';
			echo "&nbsp;&nbsp;&nbsp;&nbsp;<a href='/port_upload'>Go to back</a>";

			echo "<table>";
			echo "<th>COUNTRY</th>";
			echo "<th>PORT</th>";
			echo "<th>TO</th>";
			echo "<th>CC</th>";
			echo "<th>INTERNAL</th>";
			echo "<th>INTERNAL2</th>";

			foreach ($data as $key => $value) {
				echo "<tr>";
				echo "<td>" . $value['country'] . "</td>";
				echo "<td>" . $value['port'] . "</td>";
				echo "<td>" . $value['to'] . "</td>";
				echo "<td>" . $value['cc'] . "</td>";
				echo "<td>" . $value['internal'] . "</td>";
				echo "<td>" . $value['internal2'] . "</td>";
				echo "</tr>";
			}

			echo "</table>";

			// exit();

			// if (count($rows) === 0) {
			// 	throw new Exception("ไม่พบข้อมูล");
			// }

			// return $this->view->render('pages/port/port_export', [
			// 	"rows" => $rows
			// ]);
		} catch (\Exception $e) {
			echo "Error : " . $e->getMessage() . " <a href='/port_upload'>ย้อนกลับ</a>";
		}
	}

	public function portAll($request, $response, $args)
	{
		$parsedBody = $request->getParsedBody();

		$filter = [
			"EmailType" => "ET.Description",
			"EmailCategory" => "EC.Description"
		];

		$data = $this->port->getPortActive($this->datatables->filter($parsedBody, $filter));
		$pack = $this->datatables->get($data, $parsedBody);

		return $response->withJson($pack);
	}

	public function getDataEMail($rows = [], $country, $port, $type, $category)
	{
		try {
			$email = [];
			foreach ($rows as $key => $value) {
				if ($value['Country'] == $country && $value['Port'] == $port && $value['EmailType'] == $type && $value['EmailCategoryID'] == $category) {
					array_push($email, $value['Email']);
				}
			}
			return implode('; ', $email);
		} catch (\Exception $e) {
			return $e->getMessage();
		}
	}
}
