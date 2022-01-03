<?php

$app->group('/automail/shipmentplan', function() use ($app, $auth, $accessPage) {
	$app->get('/all', 'App\ShipmentPlan\ShipmentPlanController:all')->add($auth);
	$app->post('/getLogs', 'App\ShipmentPlan\ShipmentPlanController:getLogs')->add($auth);
});