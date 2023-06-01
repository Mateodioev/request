<?php

namespace Mateodioev\Request\Clients;

use CurlHandle;
use Mateodioev\Request\{Methods, Response, Utils};
use Mateodioev\Request\Exceptions\{CurlException, InvalidUrlException};
use Mateodioev\Utils\Exceptions\RequestException;

class Curl implements HttpClient
{
    const VERSION = '3.0-beta';
    public static string $userAgent = 'mateodioev/request v' . self::VERSION;

    public string $url;
    private CurlHandle $ch;

    /**
     * @var array<string, string> Request headers
     */
    protected array $headers = [];
    private \stdClass $headerCallback;

    private function __construct()
    {
        $this->headerCallback = new \stdClass();
        $this->headerCallback->raw = '';
        $this->headerCallback->array = [];

        $this->ch = curl_init();
        $this->addOpts([
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 60,
            CURLINFO_HEADER_OUT    => true,
            CURLOPT_HEADERFUNCTION => self::createHeaderCallback($this->headerCallback)
        ]);
    }

    /**
     * Create new instance
     */
    private static function init(): static
    {
        return (new static())
            ->setUserAgent(self::$userAgent);
    }

    /**
     * @inheritDoc
     */
    public static function GET(string $url): static
    {
        return  self::init()
            ->setUrl($url)
            ->addOpt(CURLOPT_HTTPGET, true);
    }

    /**
     * @inheritDoc
     */
    public static function POST(string $url, mixed $body): static
    {
        return self::init()
            ->setUrl($url)
            ->addOpts([
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body
            ]);
    }

    /**
     * Create new custom HTTP request (put, update, delete, etc)
     */
    public static function custom(string $url, Methods $method): static
    {
        return self::init()
            ->setUrl($url)
            ->setMethod($method);
    }

    /**
     * Create a custom function to log headers
     * @see https://www.php.net/manual/es/function.curl-setopt.php (CURLOPT_HEADERFUNCTION)
     */
    private static function createHeaderCallback(\stdClass $headerCallback): callable
    {
        return static function ($_, $header) use ($headerCallback): int {
            $headerCallback->raw .= $header;
            $currentHeader = \trim($header);

            if (!empty($currentHeader)) {
                if (str_contains($currentHeader, ':')) {
                    $headers = \explode(':', $currentHeader, 2);

                    $key = \trim($headers[0] ?? 'http');
                    $headerCallback->array[$key][] = \trim($headers[1] ?? '');
                } else {
                    $headerCallback->array['scheme'][] = $currentHeader;
                }
            }

            return strlen($header);
        };
    }

    /**
     * @inheritDoc
     */
    public function setHeaders(array $headers): static
    {
        $this->headers = [...$headers, ...$this->headers];

        return $this->addOpt(CURLOPT_HTTPHEADER, $this->headers);
    }

    /**
     * @inheritDoc
     */
    public function addHeader(string $key, string|\Stringable $value): static
    {
        return $this->setHeaders([
            \strtolower($key) => (string) $value
        ]);
    }

    /**
     * @inheritDoc
     */
    public function setBody(mixed $body): static
    {
        // Encode body if content-type == application/json
        $isJson = ($this->headers['content-type'] ?? '') == 'application/json';
        $body = ($isJson && is_array($body)) ? \json_encode($body) : $body;

        return $this->addOpt(CURLOPT_POSTFIELDS, $body);
    }

    /**
     * @inheritDoc
     * @throws InvalidUrlException
     */
    public function setUrl(string $url): static
    {
        Utils::ValidateUrl($url);
        $this->url = $url;
        $this->addOpt(CURLOPT_URL, $url);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setMsTimeout(int $milliseconds): static
    {
        return $this->addOpt(CURLOPT_TIMEOUT_MS, $milliseconds);
    }

    /**
     * @inheritDoc
     */
    public function setTimeout(int $seconds): static
    {
        return $this->addOpt(CURLOPT_TIMEOUT, $seconds);
    }

    /**
     * @inheritDoc
     */
    public function setMethod(Methods $method): static
    {
        return $this->addOpt(CURLOPT_CUSTOMREQUEST, $method->name);
    }

    public function setUserAgent(string $useragent): static
    {
        return $this->addOpt(CURLOPT_USERAGENT, $useragent);
    }

    /**
     * @inheritDoc
     */
    public function addOpts(array $opts): static
    {
        \curl_setopt_array($this->ch, $opts);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addOpt(int $key, mixed $value): static
    {
        \curl_setopt($this->ch, $key, $value);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function run(string|array|null $endpoint = null): Response
    {
        $endpoint = \is_array($endpoint) ? ('?' . \http_build_query($endpoint)) : $endpoint;
        $url = $this->url . $endpoint;

        try {
            Utils::ValidateUrl($url);
            $this->setUrl($url);
        } catch (InvalidUrlException) {
            $this->setUrl($this->url);
        }

        $response = \curl_exec($this->ch);

        if ($response === false) {
            $errno = \curl_errno($this->ch);
            $error = \curl_error($this->ch);

            \curl_close($this->ch);
            throw CurlException::curlError($error, $errno);
        }

        $info    = (array) \curl_getinfo($this->ch);
        $headers = new \stdClass();

        $headers->request  = Utils::parseHeadersHandle($info['request_header']);
        $headers->response = $this->headerCallback->array;

        \curl_close($this->ch);

        return new Response($response, $headers, $info);
    }

    /**
     * Return CurlHandle instance
     */
    public function getCurlInstance(): CurlHandle
    {
        return $this->ch;
    }
}
