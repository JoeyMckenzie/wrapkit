<?php

declare(strict_types=1);

namespace Wrapkit\Testing;

final readonly class TestRequest
{
    /**
     * @param  list<mixed>  $args
     */
    public function __construct(
        public string $resource,
        public string $method,
        public array $args)
    {
        //
    }
}
