<?php

$app->get('/media_upload', 'App\Media\MediaController:all');
$app->get('/media_upload/shipping', 'App\Media\MediaController:shipping');
$app->get('/media_upload/booking', 'App\Media\MediaController:booking');
$app->get('/media_upload/commercial', 'App\Media\MediaController:commercial');
$app->get('/media_upload/TireGroup_airwaybill', 'App\Media\MediaController:TireGroup_airwaybill');
$app->get('/media_upload/TireGroup_shipping', 'App\Media\MediaController:TireGroup_shipping');
$app->get('/media_upload/TireGroup_vessel', 'App\Media\MediaController:TireGroup_vessel');
$app->get('/media_upload/TireGroup_loading', 'App\Media\MediaController:TireGroup_loading');
$app->get('/media_upload/TireGroup_booking_new', 'App\Media\MediaController:TireGroup_booking_new');
$app->get('/media_upload/TireGroup_booking_revise', 'App\Media\MediaController:TireGroup_booking_revise');
$app->get('/media_upload/camso', 'App\Media\MediaController:camso');
$app->get('/media_upload/tiregroup', 'App\Media\MediaController:tiregroup');
$app->get('/media_upload/aot', 'App\Media\MediaController:aot');
$app->get('/media_upload/tiregroup/shippingdoc', 'App\Media\MediaController:tireGroup_shippingdoc');
$app->get('/media_upload/tiregroup/booking', 'App\Media\MediaController:tireGroup_booking');
$app->get('/media_upload/tiregroup/logs/shipping', 'App\Media\MediaController:tiregroup_logs_shipping');
$app->get('/media_upload/aot/logs', 'App\Media\MediaController:aot_logs');
$app->get('/media_upload/api', 'App\Media\MediaController:api');
$app->get('/media_upload/camso/logs', 'App\Media\MediaController:camso_logs');
$app->get('/media_upload/customs/invoice', 'App\Media\MediaController:cust_inv');
$app->get('/media_upload/shortship', 'App\Media\MediaController:shortship');
$app->get('/media_upload/api/shipping', 'App\Media\MediaController:api_shipping');
$app->get('/media_upload/api/shipping/logs', 'App\Media\MediaController:api_shipping_logs');
$app->get('/media_upload/shipping/logs', 'App\Media\MediaController:shipping_logs');
