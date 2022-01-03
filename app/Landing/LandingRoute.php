<?php

$app->get('/', 'App\Landing\LandingController:home')->add($auth);
$app->get('/login', 'App\Landing\LandingController:login');
$app->get('/forgot_password', 'App\Landing\LandingController:forgotPassword');
$app->get('/unauthorize', 'App\Landing\LandingController:unauthorize');
$app->get('/notfound', 'App\Landing\LandingController:notfound');
$app->get('/new_password/{email}/{nonce}', 'App\Landing\LandingController:newPassword');
$app->get('/register', 'App\Landing\LandingController:register');
