<?php

$app->get('/port', 'App\Port\PortController:index')->add($auth);
$app->get('/port_upload', 'App\Port\PortController:portUpload')->add($auth);

$app->group('/api/v1/port', function () use ($app, $auth, $accessApi) {
  $app->post('/all', 'App\Port\PortController:all')->add($auth);
  $app->get('/load/type/{customerport}', 'App\Port\PortController:portType')->add($auth);
  $app->post('/load/email', 'App\Port\PortController:portEmail')->add($auth);
  $app->get('/load/category/{project_id}', 'App\Port\PortController:getEmailCategory')->add($auth);
  $app->post('/create', 'App\Port\PortController:create')->add($auth);
  $app->post('/delete', 'App\Port\PortController:delete')->add($auth);
  $app->post('/update', 'App\Port\PortController:update')->add($auth);
  $app->post('/upload', 'App\Port\PortController:upload')->add($auth);
  $app->post('/upload_project', 'App\Port\PortController:project')->add($auth);
  $app->post('/export_port', 'App\Port\PortController:export')->add($auth);
  $app->post('/port_all', 'App\Port\PortController:portAll')->add($auth);
});