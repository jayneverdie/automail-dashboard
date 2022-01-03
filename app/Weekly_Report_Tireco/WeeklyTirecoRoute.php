<?php

$app->group('/automail/WeeklyTireco', function () use ($app, $auth, $accessPage) {
	$app->get('/weekly', 'App\Weekly_Report_Tireco\WeeklyTirecoController:sendmail');
	$app->get('/weekly/tireco', 'App\Weekly_Report_Tireco\WeeklyTirecoController:sendmailTireco');
});
