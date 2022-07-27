<?php 

namespace Mateodioev\Request;

use stdClass;
use function json_decode, json_last_error_msg;

class RequestResponse
{
  private string|stdClass $body = '';
  private stdClass $headers;
  private stdClass $error;

  private bool $debug = false;



  public function __construct($body, stdClass $headers, stdClass $errors) {
    $this->body = $body;
    $this->headers = $headers;
    $this->error = $errors;
  }

  /**
   * Enable debug info in __toString
   */
  public function setDebug(bool $debug = true): RequestResponse
  {
    $this->debug = $debug;
    return $this;
  }

  public function debugInfo()
  {
    $headers_request = $this->headers->request;
    $header_request = '';

    foreach ($headers_request as $key => $value) {
      $header_request .= $key . ': ' . implode(',', $value) . PHP_EOL;
    }

    $headers_response = $this->headers->response;
    $header_response = '';

    foreach ($headers_response as $key => $value) {
      $header_response .= $key . ': ' . implode(',', $value) . PHP_EOL;
    }

    $error = '';
    if ($this->isError()) {
      $error = '# Error ' . PHP_EOL . $this->getErrorMessage() . PHP_EOL;
    }
    $context = <<<EOF
# Headers-request
{$header_request}
# Headers-response
{$header_response}
{$error}
# Body
{$this->body}
EOF;
    return $context;
  }

  /**
   * Convert response body to json
   */
  public function toJson(bool $convert = false, bool $asociative = false): RequestResponse
  {
    if (!$convert) return $this;
    $body = $this->body;

    try {
      $this->body = json_decode($body, $asociative);
    } catch (\Throwable $e) {
      if (json_last_error_msg() != 'No error') {
        $this->body = $body; // Return original
        throw new ResponseException('Fail to decode json body: ' . json_last_error_msg());        
      } else {
        throw new ResponseException($e->getMessage());
      }
    }

    return $this;
  }

  /**
   * Return true if exists curl error
   */
  public function isError(): bool
  {
    return $this->error->error ?? false;
  }

  /**
   * Return error message
   */
  public function getErrorMessage(): string
  {
    return $this->error->message ?? '';
  }

  public function getHeaderRequest(string $key): array
  {
    return $this->headers->request[$key] ?? [];
  }

  public function getHeaderResponse(string $key): array
  {
    return $this->headers->response[$key] ?? [];
  }
  
  public function getBody(): string|stdClass
  {
    return $this->body;
  }

  public function __toString()
  {
    if (!is_string($this->body)) {
      $this->body = var_export($this->body, true);
    }
    
    if ($this->debug) {
      return $this->debugInfo();
    } else {
      return $this->body;
    }
  }
}
