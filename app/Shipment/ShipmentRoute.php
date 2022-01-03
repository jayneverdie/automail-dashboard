<?php

	$app->group('/automail/shipment', function() use ($app, $auth, $accessPage) {
		$app->get('/daily', 'App\Shipment\ShipmentController:sendmaildaily');
	});
