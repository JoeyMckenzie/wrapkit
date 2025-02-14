<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Testing\Concerns;

use HetznerCloud\HttpClientUtilities\Testing\AbstractDataFixture;

/**
 * @template-covariant TData of array
 */
trait Fakeable
{
    /**
     * @param  array<string, mixed>  $override
     * @param  class-string<AbstractDataFixture>  $fixture
     */
    public static function fake(string $fixture, array $override = []): static
    {
        /** @var array<string, mixed> $currentAttributes */
        $currentAttributes = $fixture::data();

        /** @var TData $attributes */
        $attributes = self::buildAttributes($currentAttributes, $override);

        return static::from($attributes);
    }

    /**
     * @param  array<array-key, mixed>  $original
     * @param  array<array-key, mixed>  $override
     * @return array<array-key, mixed>
     */
    private static function buildAttributes(array $original, array $override): array
    {
        $new = [];

        foreach ($original as $key => $entry) {
            /** @var mixed $value */
            $value = $override[$key] ?? null;

            $new[$key] = is_array($entry) && is_array($value)
                ? self::buildAttributes($entry, $value)
                : ($value ?? $entry);

            unset($override[$key]);
        }

        // Append all remaining overrides
        foreach ($override as $key => $value) {
            $new[$key] = $value;
        }

        return $new;
    }
}
