<?php

declare(strict_types=1);

namespace Wrapkit\Contracts;

interface ResourceContract
{
    /**
     * @var class-string
     */
    public string $resource {
        get;
    }
}
