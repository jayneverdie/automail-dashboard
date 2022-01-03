<?php

namespace App\Logs;

use App\Common\View;
use App\Logs\LogsAPI;
use App\Common\Datatables;

class LogsController
{
	public function __construct() {
		$this->view = new View;
		$this->logs = new LogsAPI;
		$this->datatables = new Datatables;
	}

	public function logSendmail($request, $response, $args) {
		return $this->view->render('pages/log_sendmail');
	}

	public function EmailLists($request, $response, $args) {

		$parsedBody = $request->getParsedBody();

		$data = $this->logs->EmailLists($this->datatables->filter($parsedBody),$args['column']);
		$pack = $this->datatables->get($data, $parsedBody);

		return $response->withJson($pack);
	}

	public function ListsColumn($request, $response, $args) {
		$parsedBody = $request->getParsedBody();
		
		$data = $this->logs->ListsColumn($args['table']);

		return $response->withJson($data);
	}

	public function allLogSenmail($request, $response, $args) {

		$parsedBody = $request->getParsedBody();

		$data = $this->logs->allLogSenmail($this->datatables->filter($parsedBody));
		$pack = $this->datatables->get($data, $parsedBody);

		return $response->withJson($pack);
	}

	public function allLogs($request, $response, $args) {

		$parsedBody = $request->getParsedBody();

		$data = $this->logs->allLogs($this->datatables->filter($parsedBody));
		$pack = $this->datatables->get($data, $parsedBody);

		return $response->withJson($pack);
	}

}