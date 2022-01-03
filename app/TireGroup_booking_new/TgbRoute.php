<?php

$app->group('/automail/TireGroup_booking_new', function() use ($app, $auth, $accessPage) {
	$app->get('/all', 'App\TireGroup_booking_new\TgbController:all')->add($auth);
	//$app->get('/all/send', 'App\Shipping\ShippingController:allSend');
});

$app->group('/api/v1/automail/TireGroup_booking_new', function() use ($app, $auth, $accessApi) {
	$app->post('/all/logs', 'App\TireGroup_booking_new\TgbController:getLogs')->add($auth);
});
