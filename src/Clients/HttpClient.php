<?php

namespace Mateodioev\Request\Clients;

use Mateodioev\Request\{Methods, Response};

interface HttpClient
{
    /**
     * Create new http GET request
     */
    public static function GET(string $url): static;

    /**
     * Create new http POST request
     */
    public static function POST(string $url, mixed $body): static;

    /**
     * @param array<string, string> $headers
     * 
     * Example:
     * ```php
     * static::setHeaders(['Content-Type' => 'application/json']])
     * ```
     */
    public function setHeaders(array $headers): static;

    /**
     * Add a header to the request.
     * 
     * Example:
     * ```php
     * static::addHeader('Content-Type', 'application/json')
     * ```
     */
    public function addHeader(string $key, string $value): static;

    /**
     * Set content body of the request.
     * If isset headers content-type = 'application/json' then body will be json encoded.
     */
    public function setBody(mixed $body): static;

    /**
     * Set url of the request.
     */
    public function setUrl(string $url): static;

    /**
     * Set timeout of the request in milliseconds.
     */
    public function setMsTimeout(int $milliseconds): static;

    /**
     * Set timeout of the request in seconds.
     */
    public function  setTimeout(int $seconds): static;

    /**
     * Set method of the request.
     */
    public function setMethod(Methods $method): static;

    /**
     * Set custom options for the request.
     * @see static::addOpt()
     */
    public function addOpts(array $opts): static;

    /**
     * Set custom option for the request.
     * 
     * Example:
     * ```php
     * static::addOpt(CURLOPT_SSL_VERIFYPEER, false)
     * ```
     */
    public function addOpt(int $key, mixed $value): static;

    /**
     * Run the request.
     */
    public function run(string|array|null $endpoint = null): Response;
}
