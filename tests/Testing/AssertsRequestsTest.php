<?php

declare(strict_types=1);

namespace Tests\Testing;

use Exception;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\Stubs\TestResource;
use Tests\Stubs\TestResponse;
use Wrapkit\Contracts\ResponseContract;
use Wrapkit\Testing\Concerns\AssertsRequests;
use Wrapkit\Testing\TestRequest;

covers(AssertsRequests::class);

describe(AssertsRequests::class, function (): void {
    it('can record requests and verify they were sent', function (): void {
        // Arrange
        $response = new TestResponse;
        $resource = new TestResource([$response]);

        // Act
        $recordedResponse = $resource->record(new TestRequest(
            resource: 'servers',
            method: 'GET',
            args: ['server-1']
        ));

        // Assert
        expect($recordedResponse)->toBeInstanceOf(ResponseContract::class)
            ->and($recordedResponse['foo'])->toBe('bar');

        $resource->assertSent('servers');
    });

    it('can verify requests with specific method and arguments', function (): void {
        // Arrange
        $resource = new TestResource([new TestResponse]);

        // Act
        $resource->record(new TestRequest('servers', 'POST', ['foo' => 'bar']));

        // Assert
        $resource->assertSent('servers', fn (string $method, array $args): bool => $method === 'POST' && $args['foo'] === 'bar');
    });

    it('can verify requests were sent multiple times', function (): void {
        // Arrange
        $resource = new TestResource([
            new TestResponse,
            new TestResponse,
        ]);

        // Act
        $resource->record(new TestRequest('servers', 'GET', []));
        $resource->record(new TestRequest('servers', 'GET', []));

        // Assert
        $resource->assertSent('servers', 2);
    });

    it('throws when no fake responses are available', function (): void {
        // Arrange
        $resource = new TestResource;

        // Act & Assert
        $resource->record(new TestRequest('servers', 'GET', []));
    })->throws(Exception::class, 'No fake responses left.');

    it('can verify no requests were sent', function (): void {
        // Resource
        $resource = new TestResource;

        // Act & Assert
        $resource->assertNothingSent();
    });

    it('can verify specific requests were not sent', function (): void {
        // Arrange
        $resource = new TestResource([new TestResponse]);

        // Act
        $resource->record(new TestRequest('servers', 'GET', []));

        // Assert
        $resource->assertNotSent('volumes');
    });

    it('can handle throwing responses', function (): void {
        // Arrange
        $exception = new Exception('API Error');
        $resource = new TestResource([$exception]);

        // Act & Assert
        $resource->record(new TestRequest('servers', 'GET', []));
    })->throws(Exception::class, 'API Error');

    it('can add responses after initialization', function (): void {
        // Arrange
        $resource = new TestResource;
        $response = new TestResponse;

        // Act
        $resource->addResponses([$response]);
        $recordedResponse = $resource->record(new TestRequest('servers', 'GET', []));

        // Assert
        expect($recordedResponse['foo'])->toBe('bar');
    });

    it('verifies correct error message when unexpected requests were sent', function (): void {
        // Arrange
        $resource = new TestResource([new TestResponse]);
        $resource->record(new TestRequest('servers', 'GET', []));

        // Act & Assert
        $resource->record(new TestRequest('volumes', 'POST', []));
    })->throws(Exception::class, 'No fake responses left.');

    it('fails assertion when request is sent wrong number of times', function (): void {
        // Arrange
        $resource = new TestResource([new TestResponse, new TestResponse]);
        $resource->record(new TestRequest('servers', 'GET', []));

        // Act & Assert
        $resource->assertSent('servers', 2);
    })->throws(ExpectationFailedException::class);

    it('fails with correct message when expected request is not sent', function (): void {
        // Arrange
        $resource = new TestResource;

        // Act & Assert
        $resource->assertSent('servers');
    })->throws(ExpectationFailedException::class, 'The expected resource [servers] request was not sent.');

    it('fails with correct message when unexpected request is sent', function (): void {
        // Arrange
        $resource = new TestResource([new TestResponse]);
        $resource->record(new TestRequest('servers', 'GET', []));

        // Act & Assert
        $resource->assertNotSent('servers');
    })->throws(ExpectationFailedException::class, 'The unexpected [servers] request was sent.');

    it('maintains FIFO order for responses', function (): void {
        // Arrange
        $response1 = new TestResponse('first');
        $response2 = new TestResponse('second');
        $resource = new TestResource([$response1, $response2]);

        // Act
        $first = $resource->record(new TestRequest('servers', 'GET', []));
        $second = $resource->record(new TestRequest('servers', 'GET', []));

        // Assert
        expect($first['foo'])->toBe('first')
            ->and($second['foo'])->toBe('second');
    });

    it('provides detailed error message when asserting nothing was sent', function (): void {
        // Arrange
        $proxy = new TestResource([new TestResponse, new TestResponse]);

        // Act, record multiple different requests to test the message concatenation
        $proxy->record(new TestRequest('servers', 'GET', []));
        $proxy->record(new TestRequest('volumes', 'POST', []));

        // Assert
        $proxy->assertNothingSent();
    })->throws(ExpectationFailedException::class, 'The following requests were sent unexpectedly: servers, volumes');
});
