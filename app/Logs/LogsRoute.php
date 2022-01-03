<?php

$app->get('/log_sendmail', 'App\Logs\LogsController:logSendmail') 
	->add($auth)
	->add($accessPage);

$app->group('/api/v1/logs', function() use ($app, $auth, $accessApi) {

	$app->post('/emailLists/{column}', 'App\Logs\LogsController:EmailLists')
		->add($auth);
	$app->get('/lists/column/{table}', 'App\Logs\LogsController:ListsColumn')
		->add($auth);

	$app->post('/all_logsenmail', 'App\Logs\LogsController:allLogSenmail')
		->add($auth)
		->add($accessApi);

	$app->post('/all', 'App\Logs\LogsController:allLogs')
		->add($auth);

});
