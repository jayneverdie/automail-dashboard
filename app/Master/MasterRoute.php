<?php

$app->get('/email_category', 'App\Master\MasterController:emailCategory')->add($auth);

$app->group('/api/v1/master', function () use ($app, $auth, $accessApi) {
  $app->get('/email_category', 'App\Master\MasterController:getEmailCategory')->add($auth);
  $app->post('/create_emailcategory', 'App\Master\MasterController:createEmailCategory')->add($auth);
  $app->post('/update_emailcategory', 'App\Master\MasterController:updateEmailCategory')->add($auth);

});