<?php 

namespace Mateodioev\Request;

use Mateodioev\Request\Utils;
use Exception;

class Request {
  
  public static $ch;
  public static $url;

  private static $headers;
  private static $body;
  private static $err;
  private static $error;

  private static $status;
  public static $response;
  public static $method;

  /**
   * Curl init
   * @throws Exception
   */
  public static function Init(string $url)
  {
    if (!extension_loaded('curl')) {
      throw new Exception('cURL extension is not loaded');
    } if (self::$ch) self::$ch = null;

    Utils::ValidateUrl($url);
    self::$url = $url;
    self::$ch = curl_init($url);
    self::AddOpts([CURLOPT_RETURNTRANSFER => true, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST => 0,]);
  }

  /**
   * Add multiple options to curl
   *
   * @param array $opt
   * @throws Exception
   */
  public static function AddOpts(array $opt): void
  {
    if (self::$ch === null) {
      throw new Exception("Curl not initialized");
    }
    curl_setopt_array(self::$ch, $opt);
  }

  /**
   * Add curl options
   */
  public static function AddOpt($opt, $value): void
  {
    self::AddOpts([$opt => $value]);
  }

  /**
   * Add headers to curl
   */
  public static function addHeaders(array $headers = []): void
  {
    self::$headers = array_merge(self::$headers, $headers);
    self::AddOpt(CURLOPT_HTTPHEADER, self::$headers);
  }

  /**
   * Set body to curl
   */
  public static function addBody($body): void
  {
    self::$body = $body;
    self::AddOpt(CURLOPT_POSTFIELDS, self::$body);
  }
  
  /**
   * Set curl method (GET, POST, PUT, DELETE, ETC)
   */
  public static function setMethod(string $method)
  {
    self::$method = strtoupper($method);
    self::AddOpt(CURLOPT_CUSTOMREQUEST, self::$method);
  }

  public static function Close()
  {
    curl_close(self::$ch);
    self::$ch = null;
  }

  public static function Run(bool $log = true): array
  {
    self::$response = curl_exec(self::$ch);
          $info     = curl_getinfo(self::$ch);
    self::$status   = $info['http_code'];
    self::$err      = curl_errno(self::$ch);
    self::$error    = curl_error(self::$ch);
    self::Close();

    if ($log && self::$response == false) {
      error_log('[req] Fail to send request to ' . self::$url . ' ('.self::$method.')');
      error_log('[req] Error('.self::$err.'): ' . self::$error);
    }

    return [
      'ok' => self::$response !== false,
      'info' => $info,
      'code' => self::$status,
      'response' => self::$response,
      'err' => self::$err,
      'error' => self::$error,
    ];
  }

  /**
   * Create and execute curl request
   */
  public static function Create(string $url, string $method = 'GET', ?array $headers = null, $post=null): array
  {
    self::Init($url);
    self::setMethod($method);
    if ($headers) self::addHeaders($headers);
    if ($post) self::addBody($post);
    // Execute request
    return self::Run();
  }

  /**
   * Download file and save to local
   */
  public static function Download(string $url, ?string $file_name = null)
  {
    $file_name = $file_name ?? basename($url) ?? uniqid() . 'file.tmp';
    $fp = fopen($file_name, 'wb');

    self::Init($url);
    self::AddOpts([CURLOPT_FILE => $fp, CURLOPT_HEADER => 0]);
    
    self::$response = curl_exec(self::$ch);
    self::Close();
    fclose($fp);
    return $file_name;
  }

  public static function __callStatic($method, $settings)
  {
    return self::Create(@$settings[0], $method, @$settings[1], @$settings[2]);
  }
}
