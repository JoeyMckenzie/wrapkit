<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Contracts;

use HetznerCloud\HttpClientUtilities\ValueObjects\Connector\BaseUri;
use HetznerCloud\HttpClientUtilities\ValueObjects\Connector\Headers;
use HetznerCloud\HttpClientUtilities\ValueObjects\Connector\Response;
use HetznerCloud\HttpClientUtilities\ValueObjects\Payload;

/**
 * A top-level client connector that represents communication methods with the API.
 */
interface ConnectorContract
{
    /**
     * Sends a request to the server, determining if authentication is required.
     *
     * @return null|Response<array<array-key, mixed>>
     */
    public function makeRequest(Payload $payload, ?string $accessToken): ?Response;

    /**
     * Sends a request to the server.
     *
     * @return null|Response<array<array-key, mixed>>
     */
    public function requestData(Payload $payload): ?Response;

    /**
     * Sends a request to the server with an access token.
     *
     * @return null|Response<array<array-key, mixed>>
     */
    public function requestDataWithAccessToken(Payload $payload, string $accessToken): ?Response;

    public function getHeaders(): Headers;

    public function getBaseUri(): BaseUri;
}
