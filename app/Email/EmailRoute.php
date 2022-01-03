<?php

$app->get('/email_mapping', 'App\Email\EmailController:emailMapping')
	->add($auth)
	->add($accessPage);

$app->get('/email_lists', 'App\Email\EmailController:emailLists')
	->add($auth)
	->add($accessPage);

$app->group('/api/v1/email', function() use ($app, $auth, $accessApi) {
	$app->post('/all_mapping', 'App\Email\EmailController:all')
		->add($auth)
		->add($accessApi);
	
	$app->post('/update', 'App\Email\EmailController:update')
		->add($auth)
		->add($accessApi);

	$app->post('/update_emailLists', 'App\Email\EmailController:updateEmailLists')
		->add($auth)
		->add($accessApi);

	$app->post('/create_lists', 'App\Email\EmailController:createLists')
		->add($auth);

	$app->post('/delete_lists', 'App\Email\EmailController:DeleteLists')
		->add($auth);
	
	$app->get('/lists_generate', 'App\Email\EmailController:listsGenerate')
		->add($auth)
		->add($accessApi);

	$app->post('/lists', 'App\Email\EmailController:getEmailLists')
		->add($auth)
		->add($accessApi);

	$app->get('/lists_category', 'App\Email\EmailController:getEmailCategory')
		->add($auth);

	$app->get('/lists_type', 'App\Email\EmailController:getEmailType')
		->add($auth);

	$app->get('/lists_project', 'App\Email\EmailController:getEmailProject')
		->add($auth);

	$app->post('/mapping/delete', 'App\Email\EmailController:deleteEmailMapping')
		->add($auth)
		->add($accessApi);
	
	$app->post('/mapping/create', 'App\Email\EmailController:createEmailMapping')
		->add($auth)
		->add($accessApi);
});