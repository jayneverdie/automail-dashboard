<?php

$app->group('/automail/commercial', function() use ($app, $auth, $accessPage) {
	$app->get('/all', 'App\Commercial\CommercialController:all')->add($auth);
	$app->post('/getLogs', 'App\Commercial\CommercialController:getLogs')->add($auth);
});