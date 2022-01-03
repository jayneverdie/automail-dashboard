<?php
$app->group('/automail/custinv', function() use ($app, $auth, $accessPage) 
{
    $app->get('/bytruck', 'App\CustomsInvoice_ByTruck\CustomsInvController:bytruck');
    
});