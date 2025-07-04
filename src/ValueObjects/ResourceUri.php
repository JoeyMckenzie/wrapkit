<?php

declare(strict_types=1);

namespace Wrapkit\ValueObjects;

use Override;
use Stringable;

/**
 * A value object for representing the resource endpoint for a request.
 */
final readonly class ResourceUri implements Stringable
{
    /**
     * Creates a new resource URI value object.
     */
    private function __construct(private string $uri)
    {
        //
    }

    #[Override]
    public function __toString(): string
    {
        return $this->uri;
    }

    /**
     * Creates a new ResourceUri value object that creates the given resource.
     */
    public static function create(string $resource): self
    {
        return new self($resource);
    }

    /**
     * Creates a new ResourceUri value object that creates the given resource.
     */
    public static function update(string $resource, string|int $id): self
    {
        return self::fromId($resource, $id);
    }

    /**
     * Creates a new ResourceUri value object that creates the given resource.
     */
    public static function delete(string $resource, string|int $id): self
    {
        return self::fromId($resource, $id);
    }

    /**
     * Creates a new resource URI value object that lists the given resource.
     */
    public static function list(string $resource, ?string $suffix = null): self
    {
        $uri = isset($suffix)
            ? "$resource/$suffix"
            : $resource;

        return new self($uri);
    }

    /**
     * Creates a new resource URI value object that retrieves the given resource.
     */
    public static function retrieve(string $resource, string|int $id): self
    {
        return self::fromId($resource, $id);
    }

    private static function fromId(string $resource, string|int $id): self
    {
        return new self("$resource/$id");
    }
}
