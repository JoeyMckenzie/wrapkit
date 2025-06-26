<?php

declare(strict_types=1);

namespace Wrapkit\Contracts;

use Wrapkit\Exceptions\UnserializableResponseException;
use Wrapkit\ValueObjects\Response;
use Psr\Http\Message\ResponseInterface;

interface ResponseHandlerContract
{
    /**
     * @return Response<array<array-key, mixed>>
     *
     * @throws UnserializableResponseException
     */
    public function handle(ResponseInterface $response): Response;
}
