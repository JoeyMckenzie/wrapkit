<?php

declare(strict_types=1);

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response as PsrResponse;
use HetznerCloud\HttpClientUtilities\Contracts\ResponseHandlerContract;
use HetznerCloud\HttpClientUtilities\Enums\MediaType;
use HetznerCloud\HttpClientUtilities\Exceptions\ConnectorException;
use HetznerCloud\HttpClientUtilities\Exceptions\UnserializableResponseException;
use HetznerCloud\HttpClientUtilities\Http\Connector;
use HetznerCloud\HttpClientUtilities\Http\Handlers\ResponseHandler;
use HetznerCloud\HttpClientUtilities\Support\JsonResponseValidator;
use HetznerCloud\HttpClientUtilities\ValueObjects\Connector\BaseUri;
use HetznerCloud\HttpClientUtilities\ValueObjects\Connector\Headers;
use HetznerCloud\HttpClientUtilities\ValueObjects\Connector\QueryParams;
use HetznerCloud\HttpClientUtilities\ValueObjects\Connector\Response;
use HetznerCloud\HttpClientUtilities\ValueObjects\Payload;
use Psr\Http\Client\ClientInterface;

covers(Connector::class);

describe(Connector::class, function (): void {
    beforeEach(function (): void {
        $this->client = Mockery::mock(ClientInterface::class);
        $this->baseUri = BaseUri::from('hetzner.cloud');
        $this->headers = Headers::create()->withContentType(MediaType::JSON);
        $this->queryParams = QueryParams::create();
        $this->responseHandler = new ResponseHandler(new JsonResponseValidator);

        $this->connector = new Connector(
            $this->client,
            $this->baseUri,
            $this->headers,
            $this->queryParams,
            $this->responseHandler
        );
    });

    describe('request handling', function (): void {
        it('handles successful requests', function (): void {
            $payload = Payload::list('test.resource');
            $responseData = ['data' => 'test'];

            $this->client->shouldReceive('sendRequest')
                ->with(Mockery::type(Psr\Http\Message\RequestInterface::class))
                ->once()
                ->andReturn(new PsrResponse(
                    200,
                    ['Content-Type' => MediaType::JSON->value],
                    json_encode($responseData)
                ));

            $response = $this->connector->requestData($payload);

            expect($response)->not->toBeNull()
                ->and($response->data())->toBe($responseData);
        });

        it('handles skipped response', function (): void {
            $payload = Payload::postWithoutResponse('test.resource', ['data' => 'test']);

            $this->client->shouldReceive('sendRequest')
                ->with(Mockery::type(Psr\Http\Message\RequestInterface::class))
                ->once()
                ->andReturn(new PsrResponse(204));

            $response = $this->connector->requestData($payload);

            expect($response)->toBeNull();
        });

        it('combines query parameters from payload and connector', function (): void {
            $queryParams = QueryParams::create()->withParam('global', 'param');
            $connector = new Connector(
                $this->client,
                $this->baseUri,
                $this->headers,
                $queryParams,
                $this->responseHandler
            );

            $payload = Payload::list('test.resource', ['local' => 'param']);

            $this->client->shouldReceive('sendRequest')
                ->with(Mockery::type(Psr\Http\Message\RequestInterface::class))
                ->once()
                ->andReturnUsing(function ($request): PsrResponse {
                    $query = $request->getUri()->getQuery();
                    expect($query)->toContain('global=param')
                        ->and($query)->toContain('local=param');

                    return new PsrResponse(
                        200,
                        ['Content-Type' => MediaType::JSON->value],
                        json_encode(['data' => 'test'])
                    );
                });

            $connector->requestData($payload);
        });

        it('combines headers from payload and connector', function (): void {
            $payload = Payload::post(
                'test.resource',
                ['data' => 'test'],
                null,
                ['X-Custom' => 'value']
            );

            $this->client->shouldReceive('sendRequest')
                ->with(Mockery::type(Psr\Http\Message\RequestInterface::class))
                ->once()
                ->andReturnUsing(function ($request): PsrResponse {
                    expect($request->hasHeader('Content-Type'))->toBeTrue()
                        ->and($request->hasHeader('Accept'))->toBeTrue()
                        ->and($request->hasHeader('X-Custom'))->toBeTrue();

                    return new PsrResponse(
                        200,
                        ['Content-Type' => MediaType::JSON->value],
                        json_encode(['data' => 'test'])
                    );
                });

            $this->connector->requestData($payload);
        });
    });

    describe('authentication', function (): void {
        it('creates new instance with access token', function (): void {
            $accessToken = 'test-token';
            $newConnector = $this->connector->withAccessToken($accessToken);

            expect($newConnector)->not->toBe($this->connector)
                ->and($newConnector->getHeaders()->toArray())
                ->toHaveKey('Authorization', "Bearer $accessToken");
        });

        it('handles authenticated requests', function (): void {
            $payload = Payload::list('test.resource');
            $responseData = ['data' => 'authenticated'];
            $accessToken = 'test-token';

            $this->client->shouldReceive('sendRequest')
                ->with(Mockery::type(Psr\Http\Message\RequestInterface::class))
                ->once()
                ->andReturn(new PsrResponse(
                    200,
                    ['Content-Type' => MediaType::JSON->value],
                    json_encode($responseData)
                ));

            $response = $this->connector->requestDataWithAccessToken($payload, $accessToken);

            expect($response)->not->toBeNull()
                ->and($response->data())->toBe($responseData);
        });
    });

    describe('error handling', function (): void {
        it('wraps client exceptions', function (): void {
            $payload = Payload::list('test.resource');

            $this->client->shouldReceive('sendRequest')
                ->once()
                ->andThrow(new class extends Exception implements Psr\Http\Client\ClientExceptionInterface {});

            expect(fn () => $this->connector->requestData($payload))
                ->toThrow(ConnectorException::class);
        });

        it('validates error responses for GuzzleHttp ClientException', function (): void {
            $payload = Payload::list('test.resource');
            $request = new Request('GET', 'https://hetzner.cloud/test.resource');
            $errorResponse = new PsrResponse(
                400,
                ['Content-Type' => MediaType::JSON->value],
                json_encode(['error' => 'Bad Request'])
            );

            $this->client->shouldReceive('sendRequest')
                ->once()
                ->andThrow(new ClientException(
                    'Error response',
                    $request,
                    $errorResponse
                ));

            // The test should verify that the response handler was called
            $mockResponseHandler = Mockery::mock(ResponseHandlerContract::class);
            $mockResponseHandler->shouldReceive('handle')
                ->once()
                ->with($errorResponse, false)
                ->andReturn(null);

            $connector = new Connector(
                $this->client,
                $this->baseUri,
                $this->headers,
                $this->queryParams,
                $mockResponseHandler
            );

            expect(fn (): ?Response => $connector->requestData($payload))
                ->toThrow(ConnectorException::class);
        });

        it('validates error responses with invalid JSON', function (): void {
            $payload = Payload::list('test.resource');
            $request = new Request('GET', 'https://hetzner.cloud/test.resource');
            $errorResponse = new PsrResponse(
                400,
                ['Content-Type' => MediaType::JSON->value],
                'invalid json'
            );

            $this->client->shouldReceive('sendRequest')
                ->once()
                ->andThrow(new ClientException(
                    'Error response',
                    $request,
                    $errorResponse
                ));

            // The test should verify that the response handler throws
            $mockResponseHandler = Mockery::mock(ResponseHandlerContract::class);
            $mockResponseHandler->shouldReceive('handle')
                ->once()
                ->with($errorResponse, false)
                ->andThrow(new UnserializableResponseException(new JsonException));

            $connector = new Connector(
                $this->client,
                $this->baseUri,
                $this->headers,
                $this->queryParams,
                $mockResponseHandler
            );

            expect(fn (): ?Response => $connector->requestData($payload))
                ->toThrow(UnserializableResponseException::class);
        });

        it('distinguishes between GuzzleHttp ClientException and other ClientExceptionInterface', function (): void {
            $payload = Payload::list('test.resource');

            // Create a mock that implements ClientExceptionInterface but is not ClientException
            $nonGuzzleException = new class extends Exception implements Psr\Http\Client\ClientExceptionInterface {};

            $this->client->shouldReceive('sendRequest')
                ->once()
                ->andThrow($nonGuzzleException);

            // The response handler should not be called in this case
            $mockResponseHandler = Mockery::mock(ResponseHandlerContract::class);
            $mockResponseHandler->shouldNotReceive('handle');

            $connector = new Connector(
                $this->client,
                $this->baseUri,
                $this->headers,
                $this->queryParams,
                $mockResponseHandler
            );

            expect(fn (): ?Response => $connector->requestData($payload))
                ->toThrow(ConnectorException::class);
        });
    });

    describe('getters', function (): void {
        it('returns query parameters', function (): void {
            expect($this->connector->getQueryParams())->toBe($this->queryParams);
        });

        it('returns headers', function (): void {
            expect($this->connector->getHeaders())->toBe($this->headers);
        });

        it('returns base URI', function (): void {
            expect($this->connector->getBaseUri())->toBe($this->baseUri);
        });
    });

    describe('makeRequest method', function (): void {
        it('handles requests with and without access token', function (): void {
            $payload = Payload::list('test.resource');
            $responseData = ['data' => 'test'];
            $accessToken = 'test-token';

            // Test without access token
            $this->client->shouldReceive('sendRequest')
                ->once()
                ->andReturn(new PsrResponse(
                    200,
                    ['Content-Type' => MediaType::JSON->value],
                    json_encode($responseData)
                ));

            $response = $this->connector->makeRequest($payload, null);
            expect($response)->not->toBeNull()
                ->and($response->data())->toBe($responseData);

            // Test with access token
            $this->client->shouldReceive('sendRequest')
                ->once()
                ->andReturn(new PsrResponse(
                    200,
                    ['Content-Type' => MediaType::JSON->value],
                    json_encode($responseData)
                ));

            $response = $this->connector->makeRequest($payload, $accessToken);
            expect($response)->not->toBeNull()
                ->and($response->data())->toBe($responseData);
        });
    });
});
