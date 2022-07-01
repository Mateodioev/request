<?php 

namespace Mateodioev\Request;

use Mateodioev\Utils\{Strings, Time};
use UnexpectedValueException;

class Utils {
  
  /**
   * Validate url
   * @throws UnexpectedValueException
   */
  public static function ValidateUrl(string $url): void
  {
    if (Strings::IsValidUrl($url) !== true) {
      throw new UnexpectedValueException("Invalid url");
    }
  }

  /**
   * Get took
   * @deprecated Use `Mateodioev\Utils\Time::Took($float, $round)` instead
   */
  public static function Took(bool $float = false, int $round = 3)
  {
    return Time::Took($float, $round);
  }
}