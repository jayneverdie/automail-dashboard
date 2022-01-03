<?php

$app->group('/api/v1/employee', function() use ($app, $auth, $accessApi) {
	$app->post('/all', 'App\Employee\EmployeeController:getEmployee');
});