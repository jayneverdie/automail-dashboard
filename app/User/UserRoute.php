<?php

$app->get('/user', 'App\User\UserController:index')->add($auth);

$app->group('/user', function () use ($auth, $container, $app, $accessPage) {

  $app->get('/profile', 'App\User\UserController:profile')->add($auth);
  $app->post('/profile', 'App\User\UserController:updateProfile')->add($auth)->add($container->get('csrf'));
  $app->get('/change_password', 'App\User\UserController:changePassword')->add($auth);
  $app->post('/change_password', 'App\User\UserController:saveChangePassword')->add($auth)->add($container->get('csrf'));

  $app->get('/logout', 'App\User\UserController:userLogout')->add($auth);
  $app->post('/auth', 'App\User\UserController:userAuth')->add($container->get('csrf'));
  $app->post('/forgot_password/check', 'App\User\UserController:forgotPassword')->add($container->get('csrf'));
  $app->post('/new_password/save', 'App\User\UserController:saveNewPassword')->add($container->get('csrf'));
  $app->post('/register/save', 'App\User\UserController:registerUser')->add($container->get('csrf'));
});

$app->group('/api/v1/user', function() use ($auth, $app) {

  $app->post('/all', 'App\User\UserController:all')->add($auth);
  $app->post('/create', 'App\User\UserController:createUser')->add($auth);
  $app->post('/update', 'App\User\UserController:update')->add($auth);
  $app->post('/reset_password', 'App\User\UserController:resetPassword')->add($auth);
  $app->get('/AllLogin', 'App\User\UserController:AllLogin')->add($auth);
  $app->get('/CountUser', 'App\User\UserController:CountUser')->add($auth);
  $app->post('/getEmployee', 'App\User\UserController:getEmployee')->add($auth);
});
