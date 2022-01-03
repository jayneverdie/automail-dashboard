<?php

$app->get('/category', 'App\Category\CategoryController:index');

$app->group('/api/v1/category', function() use ($app) {
	$app->post('/all', 'App\Category\CategoryController:all');
	$app->get('/all_active', 'App\Category\CategoryController:allActive');
	$app->post('/update', 'App\Category\CategoryController:update');
	$app->post('/create', 'App\Category\CategoryController:create');
});