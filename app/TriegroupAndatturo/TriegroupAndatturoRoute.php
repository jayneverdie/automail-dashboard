<?php

$app->group('/automail/TriegroupAndatturo', function () use ($app, $auth, $accessPage) {
	$app->get('/weekly', 'App\TriegroupAndatturo\TriegroupAndatturoController:sendmail');
});
