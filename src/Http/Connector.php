<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Http;

use HetznerCloud\HttpClientUtilities\Contracts\ConnectorContract;
use HetznerCloud\HttpClientUtilities\Contracts\ResponseHandlerContract;
use HetznerCloud\HttpClientUtilities\Support\ClientRequestBuilder;
use HetznerCloud\HttpClientUtilities\ValueObjects\BaseUri;
use HetznerCloud\HttpClientUtilities\ValueObjects\Headers;
use HetznerCloud\HttpClientUtilities\ValueObjects\QueryParams;
use HetznerCloud\HttpClientUtilities\ValueObjects\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * An HTTP client connector orchestrating requests and responses to and from an API.
 */
final readonly class Connector implements ConnectorContract
{
    public function __construct(
        public ClientInterface $client,
        public BaseUri $baseUri,
        public Headers $headers,
        public QueryParams $queryParams,
        public ResponseHandlerContract $responseHandler,
    ) {
        //
    }

    public function sendClientRequest(ClientRequestBuilder $requestBuilder): Response
    {
        $request = $requestBuilder
            ->withBaseUri($this->baseUri)
            ->withHeaders($this->headers->toArray())
            ->withQueryParams($this->queryParams->toArray())
            ->build();

        $response = $this->client->sendRequest($request);

        return $this->responseHandler->handle($response);
    }

    public function sendStandardClientRequestWithType(ClientRequestBuilder $requestBuilder, string $class): mixed
    {
        throw new RuntimeException('Not implemented, may come in a future release.');
    }

    public function sendStandardClientRequest(ClientRequestBuilder $requestBuilder): ResponseInterface
    {
        $request = $requestBuilder
            ->withBaseUri($this->baseUri)
            ->withHeaders($this->headers->toArray())
            ->withQueryParams($this->queryParams->toArray())
            ->build();

        return $this->client->sendRequest($request);
    }
}
