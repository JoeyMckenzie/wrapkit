<?php

declare(strict_types=1);

namespace Wrapkit\Contracts;

use Wrapkit\Support\ClientRequestBuilder;
use Wrapkit\ValueObjects\Response;
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

    /**
     * Sends a request to the server, returning the raw response given back from the PSR request.
     *
     * @param  class-string  $class
     */
    public function sendStandardClientRequestWithType(ClientRequestBuilder $requestBuilder, string $class): mixed;
}
