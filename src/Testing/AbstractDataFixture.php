<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Testing;

abstract class AbstractDataFixture
{
    /**
     * @return array<array-key, mixed>
     */
    abstract public static function data(): array;
}
