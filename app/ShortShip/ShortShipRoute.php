<?php

$app->group('/automail/shortship', function () use ($app, $auth, $accessPage) {
});

$app->group('/api/v1/automail/shortship', function () use ($app, $auth, $accessApi) {
  $app->get("/send", "App\ShortShip\ShortShipController:send");
  $app->get("/send_test", "App\ShortShip\ShortShipController:send_test");
});
