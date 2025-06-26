<?php

declare(strict_types=1);

namespace Wrapkit\Testing;

abstract class AbstractDataFixture
{
    /**
     * @return array<array-key, mixed>
     */
    abstract public static function data(): array;
}
