<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Contracts;

use HetznerCloud\HttpClientUtilities\Exceptions\UnserializableResponseException;
use HetznerCloud\HttpClientUtilities\ValueObjects\Response;
use Psr\Http\Message\ResponseInterface;

interface ResponseHandlerContract
{
    /**
     * @return Response<array<array-key, mixed>>|null
     *
     * @throws UnserializableResponseException
     */
    public function handle(ResponseInterface $response): Response;
}
