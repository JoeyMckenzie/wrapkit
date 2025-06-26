<?php

declare(strict_types=1);

namespace Tests\ValueObjects;

use Psr\Http\Message\RequestInterface;
use Wrapkit\Enums\MediaType;
use Wrapkit\Support\ClientRequestBuilder;
use Wrapkit\ValueObjects\BaseUri;

covers(ClientRequestBuilder::class);

describe(ClientRequestBuilder::class, function (): void {
    beforeEach(function (): void {
        $this->baseUri = BaseUri::from('api.example.com');
    });

    describe('static factory methods', function (): void {
        it('creates GET request correctly', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::get('servers')
                ->withBaseUri($this->baseUri)
                ->build();

            // Assert
            expect($request)
                ->toBeInstanceOf(RequestInterface::class)
                ->and($request->getMethod())->toBe('GET')
                ->and($request->getUri()->getPath())->toBe('/servers')
                ->and($request->getHeaderLine('Accept'))->toBe('application/json');
        });

        it('creates POST request correctly', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::post('servers')
                ->withBaseUri($this->baseUri)
                ->build();

            // Assert
            expect($request)
                ->toBeInstanceOf(RequestInterface::class)
                ->and($request->getMethod())->toBe('POST')
                ->and($request->getUri()->getPath())->toBe('/servers')
                ->and($request->getHeaderLine('Content-Type'))->toBe('application/json')
                ->and($request->getHeaderLine('Accept'))->toBe('application/json');
        });

        it('creates PUT request correctly', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::put('servers', '123')
                ->withBaseUri($this->baseUri)
                ->build();

            // Assert
            expect($request)
                ->toBeInstanceOf(RequestInterface::class)
                ->and($request->getMethod())->toBe('PUT')
                ->and($request->getUri()->getPath())->toBe('/servers/123')
                ->and($request->getHeaderLine('Content-Type'))->toBe('application/json')
                ->and($request->getHeaderLine('Accept'))->toBe('application/json');
        });

        it('creates DELETE request correctly', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::delete('servers', '123')
                ->withBaseUri($this->baseUri)
                ->build();

            // Assert
            expect($request)
                ->toBeInstanceOf(RequestInterface::class)
                ->and($request->getMethod())->toBe('DELETE')
                ->and($request->getUri()->getPath())->toBe('/servers/123')
                ->and($request->getHeaderLine('Accept'))->toBe('application/json');
        });
    });

    describe('request customization', function (): void {
        it('handles query parameters correctly', function (): void {
            // Arrange
            $params = [
                'page' => 1,
                'per_page' => 25,
                'label_selector' => 'env=prod',
            ];

            // Act
            $request = ClientRequestBuilder::get('servers')
                ->withBaseUri($this->baseUri)
                ->withQueryParams($params)
                ->build();

            // Assert
            expect($request->getUri()->getQuery())
                ->toBe('page=1&per_page=25&label_selector=env%3Dprod');
        });

        it('handles request content correctly', function (): void {
            // Arrange
            $content = [
                'name' => 'my-server',
                'server_type' => 'cx11',
                'location' => 'nbg1',
            ];

            // Act
            $request = ClientRequestBuilder::post('servers')
                ->withBaseUri($this->baseUri)
                ->withRequestContent($content)
                ->build();

            // Assert
            $body = json_decode((string) $request->getBody(), true);
            expect($body)
                ->toBe($content)
                ->and($request->getHeaderLine('Content-Type'))->toBe('application/json');
        });

        it('handles custom headers correctly', function (): void {
            // Arrange
            $headers = [
                'X-Custom-Header' => 'custom-value',
                'Authorization' => 'Bearer token123',
            ];

            // Act
            $request = ClientRequestBuilder::get('servers')
                ->withBaseUri($this->baseUri)
                ->withHeaders($headers)
                ->build();

            // Assert
            expect($request->getHeaderLine('X-Custom-Header'))->toBe('custom-value')
                ->and($request->getHeaderLine('Authorization'))->toBe('Bearer token123');
        });

        it('handles different content types correctly', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::post('servers')
                ->withBaseUri($this->baseUri)
                ->withContentType(MediaType::FORM)
                ->build();

            // Assert
            expect($request->getHeaderLine('Content-Type'))
                ->toBe('application/x-www-form-urlencoded');
        });
    });

    describe('edge cases', function (): void {
        it('handles resources with leading slash correctly', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::get('/resource')
                ->withBaseUri($this->baseUri)
                ->build();

            // Assert
            expect($request->getUri()->getPath())->toBe('/resource');
        });

        it('handles empty query parameters correctly', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::get('servers')
                ->withBaseUri($this->baseUri)
                ->withQueryParams([])
                ->build();

            // Assert
            expect($request->getUri()->getQuery())->toBe('');
        });

        it('preserves query parameter types correctly', function (): void {
            // Arrange
            $params = [
                'boolean' => true,
                'integer' => 42,
                'float' => 3.14,
                'string' => 'value',
            ];

            // Act
            $request = ClientRequestBuilder::get('servers')
                ->withBaseUri($this->baseUri)
                ->withQueryParams($params)
                ->build();

            // Assert
            expect($request->getUri()->getQuery())
                ->toBe('boolean=1&integer=42&float=3.14&string=value');
        });
    });
});
