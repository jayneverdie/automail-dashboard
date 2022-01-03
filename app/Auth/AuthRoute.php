<?php

$app->post('/auth', 'App\Auth\AuthController:auth')->add($container->get('csrf'));
$app->get('/logout', 'App\Auth\AuthController:logout');