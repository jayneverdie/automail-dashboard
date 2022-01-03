<?php

namespace App\Email;

use App\Common\View;
use App\Email\EmailAPI;
use App\Email\EmailListsTable;
use App\Common\Datatables;
use App\Common\Message;

class EmailController
{
	private $view = null;
	private $email = null;
	private $email_mapping_table = null;
	private $email_lists_table = null;
	private $datatables = null;
	private $message = null;

	public function __construct()
	{
		$this->view = new View();
		$this->email = new EmailAPI();
		$this->email_mapping_table = new EmailMappingTable();
		$this->email_lists_table = new EmailListsTable();
		$this->datatables = new Datatables();
		$this->message = new Message();
	}

	public function emailMapping($request, $response, $args)
	{
		return $this->view->render('pages/email_mapping');
	}

	public function all($request, $response, $args)
	{

		$parsedBody = $request->getParsedBody();

		$data = $this->email->all($this->datatables->filter($parsedBody));
		$pack = $this->datatables->get($data, $parsedBody);

		return $response->withJson($pack);
	}

	public function update($request, $response, $args)
	{
		$parsedBody = $request->getParsedBody();

		$result = $this->email->update(
			$this->email_mapping_table->field[$parsedBody['name']],
			$parsedBody['pk'],
			$parsedBody['value'],
			$this->email_mapping_table->table
		);

		return $response->withJson($result);
	}

	public function updateEmailLists($request, $response, $args)
	{
		$parsedBody = $request->getParsedBody();

		$result = $this->email->update(
			$this->email_lists_table->field[$parsedBody['name']],
			$parsedBody['pk'],
			$parsedBody['value'],
			$this->email_lists_table->table
		);

		return $response->withJson($result);
	}

	public function createLists($request, $response, $args)
	{
		$parsedBody = $request->getParsedBody();

		if (trim($parsedBody['email_list']) === '') {
			return $response->withJson($this->message->result(false, 'Data must not be blank!'));
		}

		$result = $this->email->createLists(
			$parsedBody['email_list'],
			$parsedBody['email_type'],
			$parsedBody['project']
		);

		return $response->withJson($result);
	}

	public function DeleteLists($request, $response, $args)
	{
		$parsedBody = $request->getParsedBody();

		if (trim($parsedBody['id']) === '') {
			return $response->withJson($this->message->result(false, 'Data must not be blank!'));
		}

		$result = $this->email->DeleteLists(
			$parsedBody['id']
		);

		return $response->withJson($result);
	}

	public function emailLists($request, $response, $args)
	{
		return $this->view->render('pages/email_lists');
	}

	public function getEmailLists($request, $response, $args)
	{

		$parsedBody = $request->getParsedBody();

		$data = $this->email->getEmailLists($this->datatables->filter($parsedBody));
		$pack = $this->datatables->get($data, $parsedBody);

		return $response->withJson($pack);
	}

	public function getEmailCategory($request, $response, $args)
	{
		return $response->withJson($this->email->getEmailCategory());
	}

	public function getEmailType($request, $response, $args)
	{
		return $response->withJson($this->email->getEmailType());
	}

	public function getEmailProject($request, $response, $args)
	{
		return $response->withJson($this->email->getEmailProject());
	}

	public function deleteEmailMapping($request, $response, $args)
	{
		$parsedBody = $request->getParsedBody();
		$result = $this->email->deleteEmailMapping($parsedBody['id']);
		return $response->withJson($result);
	}

	public function createEmailMapping($request, $response, $args)
	{
		$parsedBody = $request->getParsedBody();
		$result = $this->email->createEmailMapping($parsedBody['customer'], $parsedBody['email']);
		return $response->withJson($result);
	}
}
