<?php 

namespace Mateodioev\Request;

use Exception;

class Utils {
  
  public function ValidateUrl(string $url, bool $use_regex = false)
  {
    $regex = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";

    if ($use_regex && !preg_match($regex, $url)) {
      throw new Exception("Invalid URL");
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
      throw new Exception("Invalid URL");
    }
  }
}