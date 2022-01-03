<?php

$app->group('/automail/api', function() use ($app, $auth, $accessPage) {
	$app->get('/api/booking', 'App\Api\ApiController:booking');
	$app->get('/api/shipping/cif', 'App\Api\ApiController:shipping_api_cif');
	$app->get('/api/shipping/cds', 'App\Api\ApiController:shipping_api_cds');
	$app->get('/api/shipping/doc', 'App\Api\ApiController:shipping_api_doc');
	// $app->get('/tbc/si', 'App\Aot\AotController:tbcSI');
	// $app->get('/tbc/daily', 'App\Aot\AotController:tbcDaily');
	$app->get('/cds_vgm', 'App\Api\ApiController:cds_vgm');
});
