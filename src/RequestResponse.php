<?php

namespace Mateodioev\Request;

use Mateodioev\Utils\fakeStdClass;
use stdClass;

class RequestResponse
{
    private string|fakeStdClass|stdClass|array $body = '';
    private stdClass $headers;
    private stdClass $error;
    private array $info;

    private bool $debug = false;



    public function __construct($body, stdClass $headers, stdClass $errors, array $curlInfo)
    {
        $this->body    = $body;
        $this->headers = $headers;
        $this->error   = $errors;
        $this->info    = $curlInfo;
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

        $curl_info = '';
        foreach ($this->info as $key => $value) {
            $curl_info .= $key . ': ' . var_export($value, true) . PHP_EOL;
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
# Curl information
{$curl_info}
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
        if (!$convert) {
            return $this;
        }
        $body = $this->body;

        try {
            $this->body = \json_decode($body, $asociative);
            if (!$asociative) {
                $this->body = new fakeStdClass($this->body);
            }
        } catch (\Throwable $e) {
            if (\json_last_error_msg() != 'No error') {
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

    public function getAllHeaders(): array
    {
        return [
          'request' => $this->headers->request,
          'response' => $this->headers->response
        ];
    }

    /**
     * Get a sent header
     */
    public function getHeaderRequest(string $key): array
    {
        return $this->headers->request[$key] ?? [];
    }

    /**
     * Get a received header
     */
    public function getHeaderResponse(string $key): array
    {
        return $this->headers->response[$key] ?? [];
    }

    /**
     * Get request body
     */
    public function getBody(): string|fakeStdClass|stdClass|array
    {
        return $this->body;
    }

    public function getInfo(?string $key = null): mixed
    {
        return $this->info[$key] ?? ($key ? $this->info : null);
    }

    public function __toString(): string
    {
        if ($this->debug) {
            return $this->debugInfo();
        } else {
            return $this->body;
        }
    }
}
