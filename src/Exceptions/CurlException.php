<?php

namespace Mateodioev\Request\Exceptions;

use Mateodioev\Utils\Exceptions\RequestException;

use function sprintf;

class CurlException extends RequestException
{
    public static function curlError(string $error, int $errno): static
    {
        $message = 'Curl error (%d): %s';
        return new static(sprintf($message, $errno, $error), $errno);
    }
}
