<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Contracts;

use HetznerCloud\HttpClientUtilities\Support\ClientRequestBuilder;
use HetznerCloud\HttpClientUtilities\ValueObjects\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * A top-level client connector that represents communication methods with the API.
 */
interface ConnectorContract
{
    /**
     * Sends a request to the server, returning the properly typed response.
     *
     * @return Response<array<array-key, mixed>>
     */
    public function sendClientRequest(ClientRequestBuilder $requestBuilder): Response;

    /**
     * Sends a request to the server, returning the raw response given back from the PSR request.
     */
    public function sendStandardClientRequest(ClientRequestBuilder $requestBuilder): ResponseInterface;
}
