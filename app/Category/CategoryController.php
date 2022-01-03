<?php

namespace	App\Category;

use App\Category\CategoryAPI;
use App\Common\View;
use App\Category\CategoryTable;
use App\Common\Datatables;

class CategoryController
{
	public function __construct() {
		$this->category = new CategoryAPI;
		$this->view = new View;
		$this->category_table = new CategoryTable;
		$this->datatables = new Datatables;
	}

	public function index($request, $response, $args) {
		return $this->view->render('pages/category');
	}

	public function all($request, $response, $args) {
		
		$parsedBody = $request->getParsedBody();

		$data = $this->category->all($this->datatables->filter($parsedBody));
		$pack = $this->datatables->get($data, $parsedBody);
		
		return $response->withJson($pack);
	}

	public function allActive($request, $response, $args) {
		$result = $this->category->allActive();
		return $response->withJson($result);
	}

	public function update($request, $response, $args) {
		$parsedBody = $request->getParsedBody();

	    $result = $this->category->update(
	      $this->category_table->field[$parsedBody['name']],
	      $parsedBody['pk'],
	      $parsedBody['value'],
	      $this->category_table->table
	    );

	    return $response->withJson($result);
	}

	public function create($request, $response, $args) {
		$parsedBody = $request->getParsedBody();

		if ( trim($parsedBody['category_name']) === '' ) {
			return $response->withJson($this->message->result(false, 'Data must not be blank!'));
		}

		$result = $this->category->create(
			$parsedBody['category_name']
		);

		return $response->withJson($result);
	}
}