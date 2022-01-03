<?php

$app->group('/automail/TireGroup_booking_revise', function() use ($app, $auth, $accessPage) {
	$app->get('/all', 'App\TireGroup_booking_revise\TgrController:all')->add($auth);
	//$app->get('/all/send', 'App\Shipping\ShippingController:allSend');
});

$app->group('/api/v1/automail/TireGroup_booking_revise', function() use ($app, $auth, $accessApi) {
	$app->post('/all/logs', 'App\TireGroup_booking_revise\TgrController:getLogs')->add($auth);
});
