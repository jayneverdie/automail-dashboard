<?php

function userCan($cap_slug) {
  $userApi = new \App\User\UserAPI;
  return $userApi->userCan($cap_slug);
};

function getUserData() {
  $jwt = new \App\Common\JWT;
  return $jwt->verifyToken();
}

function getSidebarMenu($head = "") {
  $menu = new \App\Menu\MenuController; 
  return $menu->generateMenuHTML($head);
}

function getFlashMessage() {
  $message = new \App\Common\Message;
  return $message->getFlashMessage();
}