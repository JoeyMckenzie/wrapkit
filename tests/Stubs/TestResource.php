<?php

declare(strict_types=1);

namespace Tests\Stubs;

use Psr\Http\Message\ResponseInterface;
use Wrapkit\Contracts\ResponseContract;
use Wrapkit\Testing\Concerns\AssertsRequests;

/**
 * @template TResponse of ResponseContract<array<array-key, mixed>>|ResponseInterface
 */
final class TestResource
{
    /**
     * @use AssertsRequests<TResponse>
     */
    use AssertsRequests;
}
