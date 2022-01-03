<?php

$app->group('/automail/loadingplan', function() use ($app, $auth, $accessPage) {
	$app->get('/all', 'App\LoadingPlan\LoadingPlanController:all')->add($auth);
	$app->post('/getLogs', 'App\LoadingPlan\LoadingPlanController:getLogs')->add($auth);
});