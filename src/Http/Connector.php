<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Http;

use Closure;
use GuzzleHttp\Exception\ClientException;
use HetznerCloud\HttpClientUtilities\Contracts\ConnectorContract;
use HetznerCloud\HttpClientUtilities\Exceptions\ConnectorException;
use HetznerCloud\HttpClientUtilities\Exceptions\UnserializableResponseException;
use HetznerCloud\HttpClientUtilities\ValueObjects\Connector\BaseUri;
use HetznerCloud\HttpClientUtilities\ValueObjects\Connector\Headers;
use HetznerCloud\HttpClientUtilities\ValueObjects\Connector\QueryParams;
use HetznerCloud\HttpClientUtilities\ValueObjects\Connector\Response;
use HetznerCloud\HttpClientUtilities\ValueObjects\Payload;
use Override;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * An HTTP client connector orchestrating requests and responses to and from Bluesky.
 */
final class Connector implements ConnectorContract
{
    /**
     * Creates a new Http connector instance.
     */
    public function __construct(
        private readonly ClientInterface $client,
        private readonly BaseUri $baseUri,
        private readonly Headers $headers,
        private readonly QueryParams $queryParams,
        private readonly ResponseHandler $responseHandler,
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function makeRequest(Payload $payload, ?string $accessToken): ?Response
    {
        return $accessToken === null
            ? $this->requestData($payload)
            : $this->requestDataWithAccessToken($payload, $accessToken);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function requestData(Payload $payload): ?Response
    {
        $request = $payload->toRequest($this->baseUri, $this->headers, $this->queryParams);
        $response = $this->sendRequest(fn (): ResponseInterface => $this->client->sendRequest($request));

        return $this->responseHandler->handle($response, $payload->skipResponse);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function requestDataWithAccessToken(Payload $payload, string $accessToken): ?Response
    {
        return self::withAccessToken($accessToken)->requestData($payload);
    }

    public function withAccessToken(string $accessToken): self
    {
        return new self(
            $this->client,
            $this->baseUri,
            $this->headers->withAccessToken($accessToken),
            $this->queryParams,
            $this->responseHandler,
        );
    }

    public function getQueryParams(): QueryParams
    {
        return $this->queryParams;
    }

    #[Override]
    public function getHeaders(): Headers
    {
        return $this->headers;
    }

    #[Override]
    public function getBaseUri(): BaseUri
    {
        return $this->baseUri;
    }

    /**
     * Sends the composed request to the server.
     *
     * @throws ConnectorException|UnserializableResponseException
     */
    private function sendRequest(Closure $callable): ResponseInterface
    {
        try {
            /** @var ResponseInterface $response */
            $response = $callable();

            return $response;
        } catch (ClientExceptionInterface $clientException) {
            if ($clientException instanceof ClientException) {
                $response = $clientException->getResponse();
                $this->responseHandler->handle($response, false); // Validate error response
            }

            throw new ConnectorException($clientException);
        }
    }
}
