<?php

$app->group('/automail/booking', function() use ($app, $auth, $accessPage) {
	$app->get('/send', 'App\Booking\BookingController:send');
});

