<?php
$app->group('/automail/freightcompaire', function() use ($app, $auth, $accessPage) 
{
    $app->get('/approvemail', 'App\FreightCompaire\FreightCompaireController:approvemail');
    $app->get('/approvepage', 'App\FreightCompaire\FreightCompaireController:approvepage');
    $app->get('/approvecomplete', 'App\FreightCompaire\FreightCompaireController:approvecomplete');

    $app->post('/approve', 'App\FreightCompaire\FreightCompaireController:approve');
});