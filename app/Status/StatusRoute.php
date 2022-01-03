<?php

$app->group('/api/v1/status', function () use ($app, $auth, $accessApi) {
  $app->get('/all', 'App\Status\StatusController:getAll')->add($auth);
});