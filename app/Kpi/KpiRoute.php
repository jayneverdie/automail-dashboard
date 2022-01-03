<?php

$app->group('/kpi', function() use ($app, $auth, $accessPage) {
	$app->get('/waiting', 'App\Kpi\KpiController:waitingView');

	$app->get('/send/approve', 'App\Kpi\KpiController:sendApprove');
	$app->post('/waiting/approve', 'App\Kpi\KpiController:approveWaiting');
	
});

