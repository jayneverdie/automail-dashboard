<?php

$app->group('/menu', function () use ($app, $auth, $accessApi) {
  $app->get('/page', 'App\Menu\MenuController:page')->add($auth);
  $app->get('/api', 'App\Menu\MenuController:api')->add($auth);
});


$app->group('/api/v1/menu', function () use ($app, $auth, $accessApi) {
  $app->post('/update', 'App\Menu\MenuController:update')->add($auth);
  $app->post('/all_page', 'App\Menu\MenuController:allPage')->add($auth);
  $app->post('/all_api', 'App\Menu\MenuController:allApi')->add($auth);
  $app->post('/create', 'App\Menu\MenuController:createMenu')->add($auth);
  $app->post('/edit', 'App\Menu\MenuController:editMenu')->add($auth);
  $app->post('/delete', 'App\Menu\MenuController:deleteMenu')->add($auth);
  $app->get('/generate', 'App\Menu\MenuController:generateMenu')->add($auth);
  $app->post('/update_capabilities', 'App\Menu\MenuController:updateCapabilities')->add($auth);
});