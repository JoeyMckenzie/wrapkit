<?php

declare(strict_types=1);

namespace Tests\Support;

use GuzzleHttp\Psr7\Response as PsrResponse;
use Wrapkit\Enums\MediaType;
use Wrapkit\Exceptions\UnserializableResponseException;
use Wrapkit\Support\JsonResponseValidator;

describe(JsonResponseValidator::class, function (): void {
    beforeEach(function (): void {
        $this->validator = new JsonResponseValidator;
    });

    it('validates decoded response type is array', function (): void {
        $response = new PsrResponse(
            400,
            ['Content-Type' => MediaType::JSON->value],
            json_encode('not an array')
        );

        // This should throw because we expect an array when decoding
        expect(fn () => $this->validator->validate($response, $response->getBody()->getContents()))
            ->toThrow(UnserializableResponseException::class);
    });

    it('throws exceptions for non-JSON content types', function (): void {
        $response = new PsrResponse(
            400,
            ['Content-Type' => 'text/plain'],
            'invalid but ignored because not JSON'
        );

        expect(fn () => $this->validator->validate($response, $response->getBody()->getContents()))
            ->toThrow(UnserializableResponseException::class);
    });

    it('throws for invalid JSON with error status and JSON content type', function (): void {
        $response = new PsrResponse(
            400,
            ['Content-Type' => MediaType::JSON->value],
            'invalid json'
        );

        expect(fn () => $this->validator->validate($response, $response->getBody()->getContents()))
            ->toThrow(UnserializableResponseException::class);
    });
});
