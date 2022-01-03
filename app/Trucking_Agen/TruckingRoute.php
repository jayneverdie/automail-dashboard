<?php
$app->group('/automail/Trucking', function () use ($app, $auth, $accessPage) {
    $app->get('/approvemail', 'App\Trucking_Agen\TruckingController:approvemail');
    // $app->get('/approvepage', 'App\FreightCompaire\FreightCompaireController:approvepage');
    // $app->get('/approvecomplete', 'App\FreightCompaire\FreightCompaireController:approvecomplete');

    // $app->post('/approve', 'App\FreightCompaire\FreightCompaireController:approve');
});
