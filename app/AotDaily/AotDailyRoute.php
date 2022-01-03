<?php

	$app->group('/automail/aotdaily', function() use ($app, $auth, $accessPage) {
		$app->get('/booking', 'App\AotDaily\AotDailyController:booking');
	});
