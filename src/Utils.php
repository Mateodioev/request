<?php

namespace Mateodioev\Request;

use Mateodioev\Request\Exceptions\InvalidUrlException;
use Mateodioev\Utils\Network;

class Utils
{
    /**
     * Validate url
     * @throws InvalidUrlException
     */
    public static function ValidateUrl(string $url): void
    {
        if (empty($url) || !@Network::IsValidUrl($url)) {
            throw InvalidUrlException::fromUrl($url);
        }
    }

    /**
     * Header callback function
     * @link https://www.php.net/manual/es/function.curl-setopt.php (CURLOPT_HEADERFUNCTION)
     */
    public static function createHeaderCallback($header_callback)
    {
        return function ($_, $header) use ($header_callback): int {
            $header_callback->rawResponseHeaders .= $header;
            $x_header = \trim($header);

            if (!empty($x_header)) {
                if (\strpos($x_header, ':') !== false) {
                    $headers = \explode(': ', $x_header, 2);
                    $key = \trim($headers[0] ?? 'http');
                    $header_callback->arrayResponseHeaders[$key][] = \trim($headers[1] ?? '');
                } else {
                    $header_callback->arrayResponseHeaders['scheme'][] = $x_header;
                }
            }
            return \strlen($header);
        };
    }

    /**
     * Parse headers info
     */
    private static function parseHeaders(string $src): array
    {
        $src = \preg_split("/\n/", $src, -1, PREG_SPLIT_NO_EMPTY);
        $headers = [];

        for ($i = 0; $i < \count($src); $i++) {
            if (\strpos($src[$i], ':') !== false) {
                list($key, $value) = \explode(':', $src[$i], 2);
                $key = \trim($key);
                $value = \trim($value);

                $headers[$key][] = $value;
            }
        }
        $headers['schema'][] = \trim($src[0]) ?? '';
        return $headers;
    }

    public static function parseHeadersHandle(string $src): array
    {
        if (empty($src)) {
            return [];
        }

        $headers = self::parseHeaders($src);

        unset($headers['request_header']);

        return $headers;
    }
}
