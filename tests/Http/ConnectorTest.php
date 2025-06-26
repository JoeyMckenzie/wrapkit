<?php

declare(strict_types=1);

namespace Tests;

use GuzzleHttp\Psr7\Response as PsrResponse;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Wrapkit\Contracts\ResponseHandlerContract;
use Wrapkit\Http\Connector;
use Wrapkit\Support\ClientRequestBuilder;
use Wrapkit\ValueObjects\BaseUri;
use Wrapkit\ValueObjects\Headers;
use Wrapkit\ValueObjects\QueryParams;
use Wrapkit\ValueObjects\Response;

covers(Connector::class);

describe(Connector::class, function (): void {
    beforeEach(function (): void {
        $this->client = mock(ClientInterface::class);
        $this->baseUri = BaseUri::from('api.hetzner.cloud/v1');
        $this->headers = Headers::create()->withAccessToken('test-token');
        $this->queryParams = QueryParams::create()->withParam('version', '2023-01-01');
        $this->responseHandler = mock(ResponseHandlerContract::class);

        $this->connector = new Connector(
            $this->client,
            $this->baseUri,
            $this->headers,
            $this->queryParams,
            $this->responseHandler,
        );
    });

    describe('sendClientRequest', function (): void {
        it('sends request with correct configuration and returns handled response', function (): void {
            // Arrange
            $requestBuilder = ClientRequestBuilder::get('servers');
            $psrResponse = new PsrResponse(200, ['Content-Type' => 'application/json'], '{"status": "success"}');
            $handledResponse = Response::from(['status' => 'success']);

            $this->client
                ->shouldReceive('sendRequest')
                ->once()
                ->withArgs(fn (RequestInterface $request): bool => $request->getUri()->getHost() === 'api.hetzner.cloud'
                    && $request->getHeaderLine('Authorization') === 'Bearer test-token'
                    && str_contains($request->getUri()->getQuery(), 'version=2023-01-01'))
                ->andReturn($psrResponse);

            $this->responseHandler
                ->shouldReceive('handle')
                ->once()
                ->with($psrResponse)
                ->andReturn($handledResponse);

            // Act
            $response = $this->connector->sendClientRequest($requestBuilder);

            // Assert
            expect($response)
                ->toBeInstanceOf(Response::class)
                ->and($response->data())->toBe(['status' => 'success']);
        });
    });

    describe('sendStandardClientRequest', function (): void {
        it('sends request and returns raw PSR-7 response', function (): void {
            // Arrange
            $requestBuilder = ClientRequestBuilder::get('servers');
            $psrResponse = new PsrResponse(200, ['Content-Type' => 'application/json'], '{"status": "success"}');

            $this->client
                ->shouldReceive('sendRequest')
                ->once()
                ->withArgs(fn (RequestInterface $request): bool => $request->getUri()->getHost() === 'api.hetzner.cloud'
                    && $request->getHeaderLine('Authorization') === 'Bearer test-token'
                    && str_contains($request->getUri()->getQuery(), 'version=2023-01-01'))
                ->andReturn($psrResponse);

            // Act
            $response = $this->connector->sendStandardClientRequest($requestBuilder);

            // Assert
            expect($response)
                ->toBeInstanceOf(ResponseInterface::class)
                ->and($response->getStatusCode())->toBe(200)
                ->and($response->getHeaderLine('Content-Type'))->toBe('application/json')
                ->and((string) $response->getBody())->toBe('{"status": "success"}');
        });
    });
});
