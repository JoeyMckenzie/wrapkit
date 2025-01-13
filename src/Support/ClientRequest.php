<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Support;

use HetznerCloud\HttpClientUtilities\Enums\HttpMethod;
use HetznerCloud\HttpClientUtilities\Enums\MediaType;
use HetznerCloud\HttpClientUtilities\ValueObjects\ResourceUri;

final readonly class ClientRequest
{
    public function __construct(
        private MediaType $accept,
        private HttpMethod $method,
        private ResourceUri $uri,
        private array $parameters = [],
        private ?MediaType $contentType = null,
        private ?array $headers = []
    ) {
        //
    }
}
