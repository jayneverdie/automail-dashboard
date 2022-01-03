<?php

namespace App\Common;

class Message 
{
	public function result($result, $message, $data = []) {
    return [
      'result' => $result,
      'message' => $message,
      'data' => $data
    ];
	}
	
	public function addFlashMessage($status, $message) {
		$_SESSION['flash_status'] = '';
    $_SESSION['flash_message'] = '';
    $_SESSION['flash_status'] = htmlspecialchars($status);
    $_SESSION['flash_message'] = htmlspecialchars($message);
	}

	public function getFlashMessage() {
    $html = '';
    if ( isset($_SESSION['flash_status']) ) {
      if ($_SESSION['flash_status'] === 'success') {
        $html .= 
        '<div class="alert alert-success alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          ' . htmlspecialchars($_SESSION['flash_message']) . '
        </div>';
      } else if ($_SESSION['flash_status'] === 'error') {
        $html .= 
        '<div class="alert alert-danger alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          ' . htmlspecialchars($_SESSION['flash_message']) . '
        </div>';
      }
      
      $_SESSION['flash_status'] = '';
      $_SESSION['flash_message'] = '';

      return $html;
    } else {
      return '';
    }
	}
}
