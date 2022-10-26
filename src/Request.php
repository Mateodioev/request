<?php

namespace Mateodioev\Request;

use Mateodioev\NetscapeCookie\FileHandler as CookieFileHandler;
use Mateodioev\Request\Utils;
use Mateodioev\Utils\Exceptions\RequestException;
use Mateodioev\Utils\Network;
use stdClass, CurlHandle;
use function curl_init, curl_setopt_array, curl_setopt, strtoupper;

class Request
{

  private stdClass $headerCallback;

  /**
   * cURL resource
   */
  protected ?CurlHandle $ch = null;

  protected string $url;

  /**
   * Supported http methods
   */
  public array $valid_methods = ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'PATCH'];

  /**
   * Error data (curl error)
   */
  public stdClass $error;

  public static array $default_opts = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HEADER => false,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_AUTOREFERER => true,
    CURLOPT_CONNECTTIMEOUT => 30,
    CURLOPT_TIMEOUT => 60,
    CURLINFO_HEADER_OUT => true,
  ];

  public function __construct() {
    $this->headerCallback = new stdClass;
    $this->headerCallback->rawResponseHeaders = '';
    $this->headerCallback->arrayResponseHeaders = [];

    $this->error  = new stdClass;
    $this->error->message = '';
    $this->error->error   = false;
  }

  /**
   * Start curlHandle
   *
   * @param string $url URL to make request
   * @param array $options An array specifying which options to set and their values. The keys should be valid curl_setopt constants or their integer equivalents
   */
  public function init(string $url, array $options = []): Request
  {
    Utils::ValidateUrl($url);
    $this->url = $url;

    if ($this->ch == null) {
      $this->ch = curl_init($this->url);
      $this->addOpt(
        CURLOPT_HEADERFUNCTION,
        Utils::createHeaderCallback($this->headerCallback)
      );
    }
    // Default CURL options
    $this->addOpts(self::$default_opts);

    if (!empty($options)) {
      return $this->addOpts($options);
    }
    return $this;
  }

  /**
   * Create methods (GET, HEAD, POST, PUT, DELETE, PATCH)
   */
  public static function __callStatic($method, $arguments): Request
  {
    $instance = new self;
    $url = array_shift($arguments);

    $instance->init($url, $arguments[0] ?? []);
    $instance->setMethod($method);

    return $instance;
  }

  /**
   * Add curl options
   */
  public function addOpts(array $opts): Request
  {
    if (!$this->ch instanceof CurlHandle) {
      throw new RequestException('Curl no init');
    }

    try {
      curl_setopt_array($this->ch, $opts);
      return $this;
    } catch (\Throwable $e) {
      throw new RequestException($e->getMessage(), 500, $e);
    }
  }

  /**
   * Add curl option
   */
  public function addOpt(int $option, mixed $value): Request
  {
    try {
      curl_setopt($this->ch, $option, $value);
      return $this;
    } catch (\Throwable $e) {
      throw new RequestException($e->getMessage(), 500, $e);
    }
  }

  /**
   * Add headers to request
   * 
   * An array of HTTP header fields to set, in the format `array('Content-type: text/plain', 'Content-length: 100')`
   */
  public function addHeaders(array $headers): Request
  {
    return $this->addOpt(CURLOPT_HTTPHEADER, $headers);
  }

  /**
   * The name of a file to save all internal cookies
   */
  public function addCookieJar(string $file)
  {
    return $this->addOpt(
      CURLOPT_COOKIEJAR,
      $file
    );
  }

  /**
   * The name of the file containing the cookie data.
   */
  public function addCookieFile(CookieFileHandler|string $cookie)
  {
    if ($cookie instanceof CookieFileHandler) {
      $cookie->save();
      $cookie = $cookie->getFileName();
    }

    return $this->addOpt(CURLOPT_COOKIEFILE, $cookie);
  }

  /**
   * Set HTTP Method (GET, HEAD, POST, PUT, DELETE, PATCH)
   */
  public function setMethod(string $method): Request
  {
    $method = strtoupper($method);

    if (in_array($method, $this->valid_methods)) {
      $this->addOpt(CURLOPT_CUSTOMREQUEST, $method);
      return $this;
    } else {
      throw new RequestException('Invalid http method: ' . $method);
    }
  }

  public function Run(?string $endpoint = null): RequestResponse
  {
    if ($endpoint) {
      if (!Network::IsValidUrl($this->url . $endpoint)) {
        $endpoint = urlencode($endpoint);
      }
      $this->addOpt(CURLOPT_URL, $this->url . $endpoint);
    }

    $response = curl_exec($this->ch);
    $info = curl_getinfo($this->ch);
    $headers = new stdClass;
    
    if ($response === false) {
      $this->error->error = true;
      $this->error->errno = curl_errno($this->ch);
      $this->error->message = curl_error($this->ch);
      $this->error->msg = 'Curl error (' . $this->error->errno . '): ' . $this->error->message;
      throw new RequestException($this->error->msg);
      
    } else {
      $headers->request = Utils::parseHeadersHandle($info['request_header']);
      $headers->response = $this->headerCallback->arrayResponseHeaders;
    }

    // Close handle
    curl_close($this->ch);

    return new RequestResponse($response, $headers, $this->error, $info);
  }

  /**
   * Return CurlHandle instance
   */
  public function getCurlInstance(): ?CurlHandle
  {
    return $this->ch;
  }

}
