<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as PsrResponse;
use HetznerCloud\HttpClientUtilities\Enums\MediaType;
use HetznerCloud\HttpClientUtilities\Exceptions\UnserializableResponseException;
use HetznerCloud\HttpClientUtilities\Http\Handlers\JsonResponseHandler;

describe(JsonResponseHandler::class, function (): void {
    beforeEach(function (): void {
        $this->handler = new JsonResponseHandler;
    });

    it('processes valid JSON response', function (): void {
        $responseData = ['data' => 'test'];
        $response = new PsrResponse(
            200,
            ['Content-Type' => MediaType::JSON->value],
            json_encode($responseData)
        );

        $result = $this->handler->handle($response);

        expect($result)->not->toBeNull()
            ->and($result->data())->toBe($responseData);
    });

    it('throws for invalid JSON after validation passes', function (): void {
        $response = new PsrResponse(
            200,
            ['Content-Type' => MediaType::JSON->value],
            'invalid json'
        );

        expect(fn () => $this->handler->handle($response, false))
            ->toThrow(UnserializableResponseException::class);
    });
});
