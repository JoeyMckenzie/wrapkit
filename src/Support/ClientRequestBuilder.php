<?php

declare(strict_types=1);

namespace Wrapkit\Support;

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\RequestInterface;
use Wrapkit\Enums\HttpMethod;
use Wrapkit\Enums\MediaType;
use Wrapkit\ValueObjects\BaseUri;

/**
 * A fluent builder for creating HTTP client requests with a chainable interface.
 */
final class ClientRequestBuilder
{
    /**
     * @var array<string, string>
     */
    private array $headers = [];

    /**
     * @var array<string, mixed>
     */
    private array $queryParams = [];

    /**
     * @var array<string, mixed>
     */
    private array $requestContent = [];

    private ?MediaType $contentType = null;

    private ?BaseUri $baseUri = null;

    private function __construct(
        private readonly HttpMethod $method,
        private readonly string $resource,
        private readonly MediaType $accept,
        private readonly ?string $suffix = null,
    ) {}

    /**
     * Creates a new builder instance for a GET request.
     */
    public static function get(string $resource, ?string $suffix = null): self
    {
        return new self(HttpMethod::GET, $resource, MediaType::JSON, $suffix);
    }

    /**
     * Creates a new builder instance for a POST request.
     */
    public static function post(string $resource): self
    {
        return new self(HttpMethod::POST, $resource, MediaType::JSON)->withContentType(MediaType::JSON);
    }

    /**
     * Creates a new builder instance for a POST request.
     */
    public static function patch(string $resource): self
    {
        return new self(HttpMethod::PATCH, $resource, MediaType::JSON)->withContentType(MediaType::JSON);
    }

    /**
     * Creates a new builder instance for a PUT request.
     */
    public static function put(string $resource, ?string $suffix = null): self
    {
        return new self(HttpMethod::PUT, $resource, MediaType::JSON, $suffix)->withContentType(MediaType::JSON);
    }

    /**
     * Creates a new builder instance for a DELETE request.
     */
    public static function delete(string $resource, ?string $suffix = null): self
    {
        return new self(HttpMethod::DELETE, $resource, MediaType::JSON, $suffix);
    }

    /**
     * Creates a new builder instance for a basic request.
     */
    public static function create(
        HttpMethod $method,
        string $resource,
        MediaType $accept = MediaType::JSON,
        ?string $suffix = null,
    ): self {
        return new self($method, $resource, $accept, $suffix);
    }

    /**
     * Sets the content type of the request.
     */
    public function withContentType(MediaType $contentType): self
    {
        $clone = clone $this;
        $clone->contentType = $contentType;

        return $clone;
    }

    /**
     * Sets the content type of the request.
     */
    public function withBaseUri(BaseUri $baseUri): self
    {
        $clone = clone $this;
        $clone->baseUri = $baseUri;

        return $clone;
    }

    /**
     * Adds multiple headers to the request.
     *
     * @param  array<string, string>  $headers
     */
    public function withHeaders(array $headers): self
    {
        $clone = clone $this;
        $clone->headers = array_merge($this->headers, $headers);

        return $clone;
    }

    /**
     * Adds a single query parameter to the request.
     */
    public function withQueryParam(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->queryParams[$key] = $value;

        return $clone;
    }

    /**
     * Adds multiple query parameters to the request.
     *
     * @param  array<string, mixed>  $params
     */
    public function withQueryParams(array $params): self
    {
        $clone = clone $this;
        $clone->queryParams = array_merge($this->queryParams, $params);

        return $clone;
    }

    /**
     * Sets the request content (body).
     *
     * @param  array<string, mixed>  $content
     */
    public function withRequestContent(array $content): self
    {
        $clone = clone $this;
        $clone->requestContent = $content;

        return $clone;
    }

    /**
     * Builds and returns a PSR-7 compatible request instance.
     */
    public function build(): RequestInterface
    {
        $psr17Factory = new Psr17Factory;
        $resource = str_starts_with($this->resource, '/')
            ? substr($this->resource, 1)
            : $this->resource;
        $uri = $this->suffix === null ?
            "$resource"
            : "$resource/$this->suffix";

        if ($this->queryParams !== []) { // @pest-mutate-ignore
            $uri .= '?'.http_build_query($this->queryParams);
        }

        $url = "$this->baseUri$uri";
        $request = $psr17Factory->createRequest($this->method->value, $url);

        if ($this->requestContent !== []) {
            $body = $psr17Factory->createStream(json_encode($this->requestContent, JSON_THROW_ON_ERROR));
            $request = $request->withBody($body);
        }

        if ($this->contentType instanceof MediaType) {
            $request = $request->withHeader('Content-Type', $this->contentType->value);
        }

        foreach ($this->headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request->withHeader('Accept', $this->accept->value);
    }

    /**
     * Adds a single header to the request.
     */
    public function withHeader(string $key, string $value): self
    {
        $clone = clone $this;
        $clone->headers[$key] = $value;

        return $clone;
    }
}
