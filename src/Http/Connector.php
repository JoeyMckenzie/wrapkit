<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Http;

use Crell\Serde\Serde;
use Crell\Serde\SerdeCommon;
use HetznerCloud\HttpClientUtilities\Contracts\ConnectorContract;
use HetznerCloud\HttpClientUtilities\Contracts\ResponseHandlerContract;
use HetznerCloud\HttpClientUtilities\Support\ClientRequestBuilder;
use HetznerCloud\HttpClientUtilities\ValueObjects\BaseUri;
use HetznerCloud\HttpClientUtilities\ValueObjects\Headers;
use HetznerCloud\HttpClientUtilities\ValueObjects\QueryParams;
use HetznerCloud\HttpClientUtilities\ValueObjects\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * An HTTP client connector orchestrating requests and responses to and from an API.
 */
final class Connector implements ConnectorContract
{
    public function __construct(
        public readonly ClientInterface $client,
        public readonly BaseUri $baseUri,
        public readonly Headers $headers,
        public readonly QueryParams $queryParams,
        public readonly ResponseHandlerContract $responseHandler,
        private ?Serde $serde = null
    ) {
        $this->serde ??= new SerdeCommon;
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
        $response = $this->sendStandardClientRequest($requestBuilder);

        return $this->serde?->deserialize($response->getBody()->getContents(), 'json', $class);
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
