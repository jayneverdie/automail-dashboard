<?php

$app->get('/projectmaster', 'App\Project\ProjectController:Master');

$app->group('/api/v1/Project', function() use ($app, $auth, $accessApi) {

	$app->get('/lists', 'App\project\ProjectController:getEmailLists');
  // $app->get('/editprojactname', 'App\project\ProjectController:updateProjectname');
  //   ->add($auth)
  //   ->add($accessApi);
	$app->post('/create', 'App\project\ProjectController:createProject')->add($auth);
	$app->post('/delete_project', 'App\project\ProjectController:deleteProject')
		->add($auth);
		$app->post('/update_project', 'App\project\ProjectController:updateProjectname')
			->add($auth);
});
