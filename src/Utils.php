<?php 

namespace Mateodioev\Request;

use Exception;

class Utils {
  
  /**
   * Validate url
   * @throws Exception
   */
  public static function ValidateUrl(string $url, bool $use_regex = false)
  {
    $regex = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";

    if ($use_regex && !preg_match($regex, $url)) {
      throw new Exception("Invalid URL");
    }
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
      throw new Exception("Invalid URL");
    }
  }

  /**
   * Get took
   */
  public static function Took(bool $float = false, int $round = 3)
  {
    $ms = time();
    $s_time = (int) $_SERVER['REQUEST_TIME'];
    if ($float) {
      $ms = (float) microtime(true);
      $s_time = (float) $_SERVER['REQUEST_TIME_FLOAT'];
    }
    return round($ms - $s_time, $round);
  }
}