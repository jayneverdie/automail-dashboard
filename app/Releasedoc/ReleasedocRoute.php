<?php

$app->group('/automail/releasedoc', function() use ($app, $auth, $accessPage) {
	$app->get('/all', 'App\Releasedoc\ReleasedocController:all')->add($auth);
	$app->post('/getLogs', 'App\Releasedoc\ReleasedocController:getLogs')->add($auth);
	$app->get('/waiting', 'App\Releasedoc\ReleasedocController:waiting')->add($auth);
	$app->post('/getWaiting', 'App\Releasedoc\ReleasedocController:getWaiting')->add($auth);
});