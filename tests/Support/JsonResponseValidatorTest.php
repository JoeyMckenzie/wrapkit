<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as PsrResponse;
use HetznerCloud\HttpClientUtilities\Enums\MediaType;
use HetznerCloud\HttpClientUtilities\Exceptions\UnserializableResponseException;
use HetznerCloud\HttpClientUtilities\Support\JsonResponseValidator;

describe('JsonResponseValidator', function (): void {
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

    describe('status code handling', function (): void {
        it('skips validation for status code 399', function (): void {
            $response = new PsrResponse(
                399,
                ['Content-Type' => MediaType::JSON->value],
                'invalid but ignored because status is 399'
            );

            expect(fn () => $this->validator->validate($response, $response->getBody()->getContents()))
                ->not->toThrow(UnserializableResponseException::class);
        });

        it('validates response for status code 400', function (): void {
            $response = new PsrResponse(
                400,
                ['Content-Type' => MediaType::JSON->value],
                'invalid json'
            );

            expect(fn () => $this->validator->validate($response, $response->getBody()->getContents()))
                ->toThrow(UnserializableResponseException::class);
        });
    });

    it('skips validation for non-JSON content types', function (): void {
        $response = new PsrResponse(
            400,
            ['Content-Type' => 'text/plain'],
            'invalid but ignored because not JSON'
        );

        expect(fn () => $this->validator->validate($response, $response->getBody()->getContents()))
            ->not->toThrow(UnserializableResponseException::class);
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
