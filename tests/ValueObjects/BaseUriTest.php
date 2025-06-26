<?php

declare(strict_types=1);

namespace Tests\ValueObjects;

use Stringable;
use Wrapkit\ValueObjects\BaseUri;

covers(BaseUri::class);

describe(BaseUri::class, function (): void {
    it('adds https protocol and trailing slash when no protocol is provided', function (): void {
        // Arrange
        $rawUri = 'hetzner.cloud';

        // Act
        $baseUri = BaseUri::from($rawUri);

        // Assert
        expect((string) $baseUri)->toBe('https://hetzner.cloud/');
    });

    it('preserves http protocol and adds trailing slash', function (): void {
        // Arrange
        $rawUri = 'http://hetzner.cloud';

        // Act
        $baseUri = BaseUri::from($rawUri);

        // Assert
        expect((string) $baseUri)->toBe('http://hetzner.cloud/');
    });

    it('preserves https protocol and adds trailing slash', function (): void {
        // Arrange
        $rawUri = 'https://hetzner.cloud';

        // Act
        $baseUri = BaseUri::from($rawUri);

        // Assert
        expect((string) $baseUri)->toBe('https://hetzner.cloud/');
    });

    it('handles domains with existing trailing slash', function (): void {
        // Arrange
        $rawUri = 'hetzner.cloud/';

        // Act
        $baseUri = BaseUri::from($rawUri);

        // Assert
        expect((string) $baseUri)->toBe('https://hetzner.cloud/');
    });

    it('handles domains with subdirectories', function (): void {
        // Arrange
        $rawUri = 'hetzner.cloud/api/v1';

        // Act
        $baseUri = BaseUri::from($rawUri);

        // Assert
        expect((string) $baseUri)->toBe('https://hetzner.cloud/api/v1/');
    });

    it('handles domains with port numbers', function (): void {
        // Arrange
        $rawUri = 'localhost:3000';

        // Act
        $baseUri = BaseUri::from($rawUri);

        // Assert
        expect((string) $baseUri)->toBe('https://localhost:3000/');
    });

    describe('string conversion', function (): void {
        it('implements Stringable interface correctly', function (): void {
            // Arrange
            $baseUri = BaseUri::from('hetzner.cloud');

            // Act
            $toString = $baseUri->__toString();
            $castString = (string) $baseUri;

            // Assert
            expect($toString)->toBe('https://hetzner.cloud/')
                ->and($castString)->toBe('https://hetzner.cloud/')
                ->and($baseUri)->toBeInstanceOf(Stringable::class);
        });
    });
});
