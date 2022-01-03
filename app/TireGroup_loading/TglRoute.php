<?php

$app->group('/automail/TireGroup_loading', function() use ($app, $auth, $accessPage) {
	$app->get('/all', 'App\TireGroup_loading\TglController:all')->add($auth);
	//$app->get('/all/send', 'App\Shipping\ShippingController:allSend');
});

$app->group('/api/v1/automail/TireGroup_loading', function() use ($app, $auth, $accessApi) {
	$app->post('/all/logs', 'App\TireGroup_loading\TglController:getLogs')->add($auth);
});
