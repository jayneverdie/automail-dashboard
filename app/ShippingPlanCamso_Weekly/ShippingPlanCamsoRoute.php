<?php

	$app->group('/automail/shippingplancamso', function() use ($app, $auth, $accessPage) {
		$app->get('/weekly', 'App\ShippingPlanCamso_Weekly\ShippingPlanCamsoController:sendmail');
	});
