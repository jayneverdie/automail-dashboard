<?php

$app->group('/automail/TireGroup_vessel', function() use ($app, $auth, $accessPage) {
	$app->get('/all', 'App\TireGroup_vessel\TgvController:all')->add($auth);
	//$app->get('/all/send', 'App\Shipping\ShippingController:allSend');
});

$app->group('/api/v1/automail/TireGroup_vessel', function() use ($app, $auth, $accessApi) {
	$app->post('/all/logs', 'App\TireGroup_vessel\TgvController:getLogs')->add($auth);
});
