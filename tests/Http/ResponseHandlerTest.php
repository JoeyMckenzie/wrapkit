<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as PsrResponse;
use HetznerCloud\HttpClientUtilities\Contracts\ResponseValidatorContract;
use HetznerCloud\HttpClientUtilities\Enums\MediaType;
use HetznerCloud\HttpClientUtilities\Exceptions\UnserializableResponseException;
use HetznerCloud\HttpClientUtilities\Http\ResponseHandler;

describe('ResponseHandler', function (): void {
    beforeEach(function (): void {
        $this->validator = Mockery::mock(ResponseValidatorContract::class);
        $this->handler = new ResponseHandler($this->validator);
    });

    it('returns null when skip response is true', function (): void {
        $response = new PsrResponse(
            200,
            ['Content-Type' => MediaType::JSON->value],
            json_encode(['data' => 'test'])
        );

        // Validator should not be called when skipping response
        $this->validator->shouldNotReceive('validate');

        expect($this->handler->handle($response, true))->toBeNull();
    });

    it('processes valid JSON response', function (): void {
        $responseData = ['data' => 'test'];
        $response = new PsrResponse(
            200,
            ['Content-Type' => MediaType::JSON->value],
            json_encode($responseData)
        );

        $this->validator->shouldReceive('validate')
            ->once()
            ->with($response, json_encode($responseData));

        $result = $this->handler->handle($response, false);

        expect($result)->not->toBeNull()
            ->and($result->data())->toBe($responseData);
    });

    it('throws when validator fails', function (): void {
        $response = new PsrResponse(
            400,
            ['Content-Type' => MediaType::JSON->value],
            'invalid json'
        );

        $this->validator->shouldReceive('validate')
            ->once()
            ->with($response, 'invalid json')
            ->andThrow(new UnserializableResponseException(new JsonException));

        expect(fn () => $this->handler->handle($response, false))
            ->toThrow(UnserializableResponseException::class);
    });

    it('throws for invalid JSON after validation passes', function (): void {
        $response = new PsrResponse(
            200,
            ['Content-Type' => MediaType::JSON->value],
            'invalid json'
        );

        $this->validator->shouldReceive('validate')
            ->once()
            ->with($response, 'invalid json');

        expect(fn () => $this->handler->handle($response, false))
            ->toThrow(UnserializableResponseException::class);
    });
});
