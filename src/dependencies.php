<?php 

$container = $app->getContainer();

$container['csrf'] = function ($c) {
  return new \Slim\Csrf\Guard;
};