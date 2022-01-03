<?php

$app->group('/automail/loading', function() use ($app, $auth, $accessPage) {
	$app->get('/send', 'App\Loading\LoadingController:send');
});

