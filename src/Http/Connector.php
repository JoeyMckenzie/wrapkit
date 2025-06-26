<?php

declare(strict_types=1);

namespace Wrapkit\Http;

use Wrapkit\Contracts\ConnectorContract;
use Wrapkit\Contracts\ResponseHandlerContract;
use Wrapkit\Support\ClientRequestBuilder;
use Wrapkit\ValueObjects\BaseUri;
use Wrapkit\ValueObjects\Headers;
use Wrapkit\ValueObjects\QueryParams;
use Wrapkit\ValueObjects\Response;
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
