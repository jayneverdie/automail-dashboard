<?php

$app->group('/automail/aot', function() use ($app, $auth, $accessPage) {
	$app->get('/tbc/vgm', 'App\Aot\AotController:tbcVGM');
	$app->get('/tbc/si', 'App\Aot\AotController:tbcSI');
	$app->get('/tbc/daily', 'App\Aot\AotController:tbcDaily');
	$app->get('/test', 'App\Aot\AotController:test');
	$app->get('/shipping/docinsp', 'App\Aot\AotController:shippingDocInsp');
});

