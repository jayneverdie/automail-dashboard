<?php

$app->get('/capability', 'App\Capability\CapabilityController:index')->add($auth);

$app->group('/api/v1/capability', function () use ($app, $auth, $accessApi) {
  $app->post('/update', 'App\Capability\CapabilityController:update')->add($auth);
  $app->post('/create', 'App\Capability\CapabilityController:create')->add($auth);
  $app->post('/delete', 'App\Capability\CapabilityController:delete')->add($auth);
  $app->post('/all', 'App\Capability\CapabilityController:all')->add($auth);
  $app->get('/all_active', 'App\Capability\CapabilityController:getActive')->add($auth);
  $app->get('/capability_by_role/{role_id}/{category_id}', 'App\Capability\CapabilityController:getCapabilityByRole')->add($auth);
  $app->get('/capability_by_category/{category_id}', 'App\Capability\CapabilityController:getCapabilityByCategory')->add($auth);
});