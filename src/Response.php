<?php

namespace Mateodioev\Request;

use Mateodioev\Request\Exceptions\ResponseException;
use stdClass;

class Response
{
    private string|stdClass|array $body = '';
    private stdClass $headers;
    private array $info;

    private bool $debug = false;



    public function __construct($body, stdClass $headers, array $curlInfo)
    {
        $this->body    = $body;
        $this->headers = $headers;
        $this->info    = $curlInfo;
    }

    /**
     * Enable debug info in __toString
     */
    public function setDebug(bool $debug = true): Response
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

        $context = <<<EOF
        # Headers-request
        {$header_request}
        # Headers-response
        {$header_response}
        # Curl information
        {$curl_info}
        # Body
        {$this->body}
        EOF;
        return $context;
    }

    /**
     * Convert response body to json
     * @throws ResponseException
     */
    public function toJson(bool $convert = false, bool $asociative = false): Response
    {
        if (!$convert) {
            return $this;
        }

        try {
            $this->body = \json_decode($this->body(), $asociative, flags: JSON_THROW_ON_ERROR);
        } catch (\Throwable $e) {
            throw new ResponseException($e->getMessage());
        }

        return $this;
    }

    public function allHeaders(): array
    {
        return [
            'request' => $this->headers->request,
            'response' => $this->headers->response
        ];
    }

    /**
     * Get a sent header
     */
    public function headerRequest(string $key): array
    {
        return $this->headers->request[$key] ?? [];
    }

    /**
     * Get a received header
     */
    public function headerResponse(string $key): array
    {
        return $this->headers->response[$key] ?? [];
    }

    /**
     * Get request body
     */
    public function body(): string|stdClass|array
    {
        return $this->body;
    }

    /**
     * Get response http code
     */
    public function httpCode(): int
    {
        return (int) $this->getInfo('http_code');
    }

    /**
     * Get curl info or a specific key
     * Pass true to get all info
     */
    public function getInfo(string|bool $key = false): mixed
    {
        return $this->info[$key] ?? ($key ? $this->info : null);
    }

    /**
     * Convert body to string
     */
    protected function bodyToString(): string
    {
        $body = $this->body();

        return match ($body) {
            \is_array($body)          => \json_encode($body),
            $body instanceof stdClass => \json_encode($body),
            default                   => $body
        };
    }

    public function __toString(): string
    {
        if ($this->debug) {
            return $this->debugInfo();
        }

        return $this->bodyToString();
    }
}
