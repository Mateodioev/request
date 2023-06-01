<?php

namespace Mateodioev\Request\Exceptions;

use Mateodioev\Utils\Exceptions\RequestException;

use function parse_url;

class InvalidUrlException extends RequestException
{
    /**
     * Create new exception from url
     */
    public static function fromUrl(string $url): static
    {
        return new static("Invalid url: $url");
    }

    /**
     * Create new exception from domain
     */
    public function fromDomain(string $url): static
    {
        $domain = self::getDomain($url);
        return new static("Invalid domain: $domain");
    }

    public static function getDomain(string $url): string
    {
        return parse_url($url, PHP_URL_HOST);
    }
}
