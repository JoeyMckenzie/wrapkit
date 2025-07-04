<?php

declare(strict_types=1);

namespace Tests\ValueObjects;

use Wrapkit\ValueObjects\ResourceUri;

covers(ResourceUri::class);

describe(ResourceUri::class, function (): void {
    it('can create a new resource URI', function (): void {
        // Arrange & Act
        $uri = ResourceUri::create('com.example.resource');

        // Assert
        expect((string) $uri)->toBe('com.example.resource');
    });

    it('can get a resource without suffix', function (): void {
        // Arrange & Act
        $uri = ResourceUri::list('com.example.resource');

        // Assert
        expect((string) $uri)->toBe('com.example.resource');
    });

    it('can get a resource with suffix', function (): void {
        // Arrange & Act
        $uri = ResourceUri::list('com.example.resource', 'list');

        // Assert
        expect((string) $uri)->toBe('com.example.resource/list');
    });

    it('handles null suffix correctly', function (): void {
        // Arrange & Act
        $uri = ResourceUri::list('com.example.resource', null);

        // Assert
        expect((string) $uri)->toBe('com.example.resource');
    });

    it('can retrieve a specific resource by ID', function (): void {
        // Arrange & Act
        $uri = ResourceUri::retrieve('com.example.resource', '123');

        // Assert
        expect((string) $uri)->toBe('com.example.resource/123');
    });

    it('can update a specific resource by ID', function (): void {
        // Arrange & Act
        $uri = ResourceUri::update('com.example.resource', '123');

        // Assert
        expect((string) $uri)->toBe('com.example.resource/123');
    });

    it('can delete a specific resource by ID', function (): void {
        // Arrange & Act
        $uri = ResourceUri::delete('com.example.resource', '123');

        // Assert
        expect((string) $uri)->toBe('com.example.resource/123');
    });

    it('converts to string using __toString', function (): void {
        // Arrange & Act
        $uri = ResourceUri::create('com.example.resource');

        // Assert
        expect($uri->__toString())->toBe('com.example.resource')
            ->and((string) $uri)->toBe('com.example.resource');
    });
});
