<?php 

namespace Mateodioev\Request;

use Mateodioev\Utils\Network;
use Mateodioev\Utils\Exceptions\RequestException;

class Utils {
  
  /**
   * Validate url
   * @throws RequestException
   */
  public static function ValidateUrl(string $url): void
  {
    if (empty($url) || !Network::IsValidUrl($url)) {
      throw new RequestException('Invalid URL'); 
    }
  }
}