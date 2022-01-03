<?php

namespace App\Common;

class Cookie
{
  public function setCookie($key, $value, int $time = 0,  $path = "/") {
    setcookie($key, $value, $time, $path, null, null, true);
  }
}