<?php

declare(strict_types=1);

namespace Wrapkit\Contracts;

use Psr\Http\Message\ResponseInterface;
use Wrapkit\Exceptions\UnserializableResponseException;
use Wrapkit\ValueObjects\Response;

interface ResponseHandlerContract
{
    /**
     * @return Response<array<array-key, mixed>>
     *
     * @throws UnserializableResponseException
     */
    public function handle(ResponseInterface $response): Response;
}
