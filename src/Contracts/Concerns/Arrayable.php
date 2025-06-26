<?php

declare(strict_types=1);

namespace Wrapkit\Contracts\Concerns;

/**
 * Provides a contract to allow resources and objects to be displayed as an array representation.
 *
 * @template-covariant TArray of array
 */
interface Arrayable
{
    /**
     * @return TArray
     */
    public function toArray(): array;
}
