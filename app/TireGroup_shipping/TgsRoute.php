<?php

$app->group('/automail/TireGroup_shipping', function() use ($app, $auth, $accessPage) {
	$app->get('/all', 'App\TireGroup_shipping\TgsController:all')->add($auth);
	$app->get('/all/send', 'App\TireGroup_shipping\TgsController:allSend');
});

$app->group('/api/v1/automail/TireGroup_shipping', function() use ($app, $auth, $accessApi) {
	$app->post('/all/logs', 'App\TireGroup_shipping\TgsController:getLogs')->add($auth);
});
