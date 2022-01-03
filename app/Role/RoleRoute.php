<?php

$app->get('/role', 'App\Role\RoleController:role')->add($auth);

$app->group('/api/v1/role', function () use ($app, $auth, $accessApi) {
  $app->post('/all', 'App\Role\RoleController:all')->add($auth);
  $app->post('/create', 'App\Role\RoleController:create')->add($auth);
  $app->get('/active', 'App\Role\RoleController:getRoleActive')->add($auth);
  $app->post('/update', 'App\Role\RoleController:update')->add($auth);
  $app->post('/update_capability', 'App\Role\RoleController:updateCapability')->add($auth);
  $app->get('/feed/menu', 'App\Role\RoleController:getMenu')->add($auth);
});