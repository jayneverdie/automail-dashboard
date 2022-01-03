<?php

$app->group('/automail/camso', function() use ($app, $auth, $accessPage) {
	$app->get('/run', 'App\Camso\CamsoController:runCamso');
	$app->get('/runacc', 'App\Camso\CamsoController:runCamsoACC');
	$app->get('/weekly', 'App\Camso\CamsoController:weeklyCamso');
});

