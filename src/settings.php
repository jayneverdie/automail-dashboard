<?php

header_remove("X-Powered-By");

return [
  'settings' => [
    'debug' => false,
    'whoops.editor' => 'sublime',
    'whoops.page_title' => 'Something wrong!',
    'displayErrorDetails' => false,
    'addContentLengthHeader' => false,
  ],
];
