<?php

$app->group('/automail/tiregroup', function() use ($app, $auth, $accessPage) {
	$app->get('/run/booking', 'App\TireGroup\TireGroupController:runTireGroupBooking');
	$app->get('/run/shipping', 'App\TireGroup\TireGroupController:runTireGroupShipping');
	// $app->get('/run/loadingplan', 'App\TireGroup\TireGroupController:runTireGroupLoadingPlan');
	$app->get('/run/vessel', 'App\TireGroup\TireGroupController:runTireGroupVessel');
	$app->get('/run/airwaybill', 'App\TireGroup\TireGroupController:runTireGroupAirwaybill');
});

