<?php

$app->group('/automail/shipping', function() use ($app, $auth, $accessPage) {
	$app->get('/all', 'App\Shipping\ShippingController:all')->add($auth);
	$app->get('/all/send', 'App\Shipping\ShippingController:allSend');
	$app->get('/all/send/confirm', 'App\Shipping\ShippingController:sendConfirm');
	$app->get('/all/send/aot', 'App\Shipping\ShippingController:sendAot');
});

$app->group('/api/v1/automail/shipping', function() use ($app, $auth, $accessApi) {
	$app->post('/all/logs', 'App\Shipping\ShippingController:getLogs')->add($auth);
});