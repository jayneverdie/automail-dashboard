<?php

$app->group('/automail/pcr', function() use ($app, $auth, $accessPage) {
	$app->get('/all', 'App\pcr\PcrController:all')->add($auth);
	//$app->get('/all/send', 'App\Shipping\ShippingController:allSend');
});

$app->group('/api/v1/automail/pcr', function() use ($app, $auth, $accessApi) {
	$app->post('/all/logs', 'App\pcr\PcrController:getLogs')->add($auth);
});
