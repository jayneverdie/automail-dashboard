<?php

namespace App\Common;

class View
{
  public function render($path, $data = null) {
		$templates = new \League\Plates\Engine(__DIR__ . '/../../views', 'tpl');
		if (isset($data)) {
			echo $templates->render($path, $data);
		} else {
			echo $templates->render($path);
		}
	}
}