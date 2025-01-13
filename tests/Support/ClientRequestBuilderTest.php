<?php

declare(strict_types=1);

namespace Tests\Support;

use HetznerCloud\HttpClientUtilities\Enums\HttpMethod;
use HetznerCloud\HttpClientUtilities\Enums\MediaType;
use HetznerCloud\HttpClientUtilities\Support\ClientRequestBuilder;

covers(ClientRequestBuilder::class);

describe(ClientRequestBuilder::class, function (): void {
    describe('factory methods', function (): void {
        it('creates GET requests', function (): void {
            // Arrange & Act
            $builder = ClientRequestBuilder::get('test.resource');
            $request = $builder->build();

            // Assert
            expect($request->getMethod())->toBe('GET')
                ->and($request->getUri()->getPath())->toBe('test.resource')
                ->and($request->getHeaderLine('Accept'))->toBe('application/json');
        });

        it('creates POST requests', function (): void {
            // Arrange & Act
            $builder = ClientRequestBuilder::post('test.resource');
            $request = $builder->build();

            // Assert
            expect($request->getMethod())->toBe('POST')
                ->and($request->getUri()->getPath())->toBe('test.resource')
                ->and($request->getHeaderLine('Accept'))->toBe('application/json');
        });

        it('creates PUT requests', function (): void {
            // Arrange & Act
            $builder = ClientRequestBuilder::put('test.resource');
            $request = $builder->build();

            // Assert
            expect($request->getMethod())->toBe('PUT')
                ->and($request->getUri()->getPath())->toBe('test.resource')
                ->and($request->getHeaderLine('Accept'))->toBe('application/json');
        });

        it('creates DELETE requests', function (): void {
            // Arrange & Act
            $builder = ClientRequestBuilder::delete('test.resource');
            $request = $builder->build();

            // Assert
            expect($request->getMethod())->toBe('DELETE')
                ->and($request->getUri()->getPath())->toBe('test.resource')
                ->and($request->getHeaderLine('Accept'))->toBe('application/json');
        });

        it('creates custom requests', function (): void {
            // Arrange & Act
            $builder = ClientRequestBuilder::create(HttpMethod::POST, 'test.resource', MediaType::MULTIPART);
            $request = $builder->build();

            // Assert
            expect($request->getMethod())->toBe('POST')
                ->and($request->getUri()->getPath())->toBe('test.resource')
                ->and($request->getHeaderLine('Accept'))->toBe('multipart/form-data');
        });
    });

    describe('header handling', function (): void {
        it('adds single headers', function (): void {
            // Arrange
            $builder = ClientRequestBuilder::get('test.resource')
                ->withHeader('X-Test', 'value');

            // Act
            $request = $builder->build();

            // Assert
            expect($request->hasHeader('X-Test'))->toBeTrue()
                ->and($request->getHeaderLine('X-Test'))->toBe('value');
        });

        it('adds multiple headers', function (): void {
            // Arrange
            $headers = [
                'X-Test-1' => 'value1',
                'X-Test-2' => 'value2',
            ];

            // Act
            $builder = ClientRequestBuilder::get('test.resource')
                ->withHeaders($headers);
            $request = $builder->build();

            // Assert
            expect($request->hasHeader('X-Test-1'))->toBeTrue()
                ->and($request->getHeaderLine('X-Test-1'))->toBe('value1')
                ->and($request->hasHeader('X-Test-2'))->toBeTrue()
                ->and($request->getHeaderLine('X-Test-2'))->toBe('value2');
        });

        it('merges headers properly', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::get('test.resource')
                ->withHeader('X-Test-1', 'value1')
                ->withHeaders([
                    'X-Test-2' => 'value2',
                    'X-Test-1' => 'new-value', // Should override
                ])
                ->build();

            // Assert
            expect($request->getHeaderLine('X-Test-1'))->toBe('new-value')
                ->and($request->getHeaderLine('X-Test-2'))->toBe('value2');
        });
    });

    describe('query parameter handling', function (): void {
        it('adds single query parameters', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::get('test.resource')
                ->withQueryParam('key', 'value')
                ->build();

            // Assert
            parse_str($request->getUri()->getQuery(), $params);
            expect($params)->toBe(['key' => 'value']);
        });

        it('adds multiple query parameters', function (): void {
            // Arrange
            $params = [
                'key1' => 'value1',
                'key2' => 'value2',
            ];

            // Act
            $request = ClientRequestBuilder::get('test.resource')
                ->withQueryParams($params)
                ->build();

            // Assert
            parse_str($request->getUri()->getQuery(), $result);
            expect($result)->toBe($params);
        });

        it('merges query parameters properly', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::get('test.resource')
                ->withQueryParam('key1', 'value1')
                ->withQueryParams([
                    'key2' => 'value2',
                    'key1' => 'new-value', // Should override
                ])
                ->build();

            // Assert
            parse_str($request->getUri()->getQuery(), $params);
            expect($params)->toBe([
                'key1' => 'new-value',
                'key2' => 'value2',
            ]);
        });

        it('handles different parameter types', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::get('test.resource')
                ->withQueryParams([
                    'string' => 'value',
                    'int' => 42,
                    'bool' => true,
                    'array' => ['a', 'b'],
                ])
                ->build();

            // Assert
            parse_str($request->getUri()->getQuery(), $params);
            expect($params)->toHaveKeys(['string', 'int', 'bool', 'array']);
        });
    });

    describe('request content handling', function (): void {
        it('adds request content to POST requests', function (): void {
            // Arrange
            $content = ['test' => 'value'];

            // Act
            $request = ClientRequestBuilder::post('test.resource')
                ->withRequestContent($content)
                ->build();

            // Assert
            $body = $request->getBody()->getContents();
            expect($body)->toBeJson()
                ->and(json_decode($body, true))->toBe($content);
        });

        it('handles nested request content', function (): void {
            // Arrange
            $content = [
                'parent' => [
                    'child' => 'value',
                    'array' => [1, 2, 3],
                ],
            ];

            // Act
            $request = ClientRequestBuilder::post('test.resource')
                ->withRequestContent($content)
                ->build();

            // Assert
            $body = $request->getBody()->getContents();
            expect($body)->toBeJson()
                ->and(json_decode($body, true))->toBe($content);
        });
    });

    describe('content type handling', function (): void {
        it('sets content type header', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::post('test.resource')
                ->withContentType(MediaType::MULTIPART)
                ->build();

            // Assert
            expect($request->hasHeader('Content-Type'))->toBeTrue()
                ->and($request->getHeaderLine('Content-Type'))->toBe('multipart/form-data');
        });

        it('defaults to JSON content type for POST requests with content', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::post('test.resource')
                ->withRequestContent(['test' => 'value'])
                ->build();

            // Assert
            expect($request->hasHeader('Content-Type'))->toBeTrue()
                ->and($request->getHeaderLine('Content-Type'))->toBe('application/json');
        });
    });

    describe('immutability', function (): void {
        it('maintains immutability when adding headers', function (): void {
            // Arrange
            $original = ClientRequestBuilder::get('test.resource');

            // Act
            $modified = $original->withHeader('X-Test', 'value');

            // Assert
            expect($original)->not->toBe($modified);

            $originalRequest = $original->build();
            expect($originalRequest->hasHeader('X-Test'))->toBeFalse();

            $modifiedRequest = $modified->build();
            expect($modifiedRequest->hasHeader('X-Test'))->toBeTrue();
        });

        it('maintains immutability when adding query parameters', function (): void {
            // Arrange
            $original = ClientRequestBuilder::get('test.resource');

            // Act
            $modified = $original->withQueryParam('test', 'value');

            // Assert
            expect($original)->not->toBe($modified);

            $originalRequest = $original->build();
            expect($originalRequest->getUri()->getQuery())->toBe('');

            $modifiedRequest = $modified->build();
            expect($modifiedRequest->getUri()->getQuery())->toBe('test=value');
        });
    });

    describe('path handling', function (): void {
        it('handles paths with and without leading slash', function (): void {
            // Arrange & Act
            $withSlash = ClientRequestBuilder::get('/test.resource')->build();
            $withoutSlash = ClientRequestBuilder::get('test.resource')->build();

            // Assert - both should produce the same result
            expect($withSlash->getUri()->getPath())
                ->toBe($withoutSlash->getUri()->getPath());
        });

        it('correctly combines path with suffix', function (): void {
            // Arrange & Act
            $request = ClientRequestBuilder::get('test.resource', 'suffix')->build();

            // Assert
            expect($request->getUri()->getPath())->toBe('test.resource/suffix');
        });
    });
});
