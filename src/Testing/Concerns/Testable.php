<?php

declare(strict_types=1);

namespace Wrapkit\Testing\Concerns;

use Givebutter\Testing\ClientFake;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Wrapkit\Contracts\ResponseContract;
use Wrapkit\Testing\TestRequest;

/**
 * @template TArray of array
 *
 * @property-read class-string $resource
 */
trait Testable
{
    public function __construct(
        private readonly ClientFake $fake
    ) {
        //
    }

    public function assertSent(?callable $callback = null): void
    {
        $this->fake->assertSent($this->resource, $callback);
    }

    public function assertNotSent(?callable $callback = null): void
    {
        $this->fake->assertNotSent($this->resource, $callback);
    }

    /**
     * @param  list<mixed>  $args
     * @param  class-string<ResponseContract|ResponseInterface>  $expectedType
     */
    protected function record(string $method, array $args = [], string $expectedType = ResponseContract::class): ResponseContract|ResponseInterface
    {
        $response = $this->fake->record(new TestRequest($this->resource, $method, $args));

        if (! $response instanceof $expectedType) {
            throw new RuntimeException(sprintf(
                'Expected response of type %s, got %s',
                $expectedType,
                $response::class
            ));
        }

        return $response;
    }
}
