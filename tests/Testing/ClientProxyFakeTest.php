<?php

declare(strict_types=1);

namespace Tests\Testing;

use Exception;
use HetznerCloud\HttpClientUtilities\Contracts\ResponseContract;
use HetznerCloud\HttpClientUtilities\Testing\ClientProxyFake;
use HetznerCloud\HttpClientUtilities\Testing\TestRequest;
use PHPUnit\Framework\ExpectationFailedException;

covers(ClientProxyFake::class);

describe(ClientProxyFake::class, function (): void {
    beforeEach(function (): void {
        $this->proxy = new ClientProxyFake;
    });

    it('can record requests and verify they were sent', function (): void {
        $response = new TestResponse;
        $proxy = new ClientProxyFake([$response]);

        $recordedResponse = $proxy->record(new TestRequest(
            resource: 'servers',
            method: 'GET',
            args: ['server-1']
        ));

        expect($recordedResponse)->toBeInstanceOf(ResponseContract::class)
            ->and($recordedResponse['foo'])->toBe('bar');

        $proxy->assertSent('servers');
    });

    it('can verify requests with specific method and arguments', function (): void {
        $proxy = new ClientProxyFake([new TestResponse]);

        $proxy->record(new TestRequest('servers', 'POST', ['foo' => 'bar']));

        $proxy->assertSent('servers', fn (string $method, array $args): bool => $method === 'POST' && $args['foo'] === 'bar');
    });

    it('can verify requests were sent multiple times', function (): void {
        $proxy = new ClientProxyFake([
            new TestResponse,
            new TestResponse,
        ]);

        $proxy->record(new TestRequest('servers', 'GET', []));
        $proxy->record(new TestRequest('servers', 'GET', []));

        $proxy->assertSent('servers', 2);
    });

    it('throws when no fake responses are available', function (): void {
        $proxy = new ClientProxyFake;

        expect(fn (): \HetznerCloud\HttpClientUtilities\Contracts\ResponseContract => $proxy->record(new TestRequest('servers', 'GET', [])))
            ->toThrow(Exception::class, 'No fake responses left.');
    });

    it('can verify no requests were sent', function (): void {
        $proxy = new ClientProxyFake;

        $proxy->assertNothingSent();
    });

    it('can verify specific requests were not sent', function (): void {
        $proxy = new ClientProxyFake([new TestResponse]);

        $proxy->record(new TestRequest('servers', 'GET', []));

        $proxy->assertNotSent('volumes');
    });

    it('can handle throwing responses', function (): void {
        $exception = new Exception('API Error');
        $proxy = new ClientProxyFake([$exception]);

        expect(fn (): \HetznerCloud\HttpClientUtilities\Contracts\ResponseContract => $proxy->record(new TestRequest('servers', 'GET', [])))
            ->toThrow(Exception::class, 'API Error');
    });

    it('can add responses after initialization', function (): void {
        $proxy = new ClientProxyFake;
        $response = new TestResponse;

        $proxy->addResponses([$response]);

        $recordedResponse = $proxy->record(new TestRequest('servers', 'GET', []));
        expect($recordedResponse['foo'])->toBe('bar');
    });

    describe('mutations', function (): void {
        it('verifies correct error message when unexpected requests were sent', function (): void {
            $proxy = new ClientProxyFake([new TestResponse]);
            $proxy->record(new TestRequest('servers', 'GET', []));

            expect(fn (): \HetznerCloud\HttpClientUtilities\Contracts\ResponseContract => $proxy->record(new TestRequest('volumes', 'POST', [])))
                ->toThrow(Exception::class, 'No fake responses left.');
        });

        it('fails assertion when request is sent wrong number of times', function (): void {
            $proxy = new ClientProxyFake([new TestResponse, new TestResponse]);
            $proxy->record(new TestRequest('servers', 'GET', []));

            expect(fn () => $proxy->assertSent('servers', 2))
                ->toThrow(ExpectationFailedException::class);
        });

        it('fails with correct message when expected request is not sent', function (): void {
            $proxy = new ClientProxyFake;

            expect(fn () => $proxy->assertSent('servers'))
                ->toThrow(ExpectationFailedException::class, 'The expected resource [servers] request was not sent.');
        });

        it('fails with correct message when unexpected request is sent', function (): void {
            $proxy = new ClientProxyFake([new TestResponse]);
            $proxy->record(new TestRequest('servers', 'GET', []));

            expect(fn () => $proxy->assertNotSent('servers'))
                ->toThrow(ExpectationFailedException::class, 'The unexpected [servers] request was sent.');
        });

        it('maintains FIFO order for responses', function (): void {
            $response1 = new TestResponse('first');
            $response2 = new TestResponse('second');
            $proxy = new ClientProxyFake([$response1, $response2]);

            $first = $proxy->record(new TestRequest('servers', 'GET', []));
            $second = $proxy->record(new TestRequest('servers', 'GET', []));

            expect($first['foo'])->toBe('first')
                ->and($second['foo'])->toBe('second');
        });

        it('provides detailed error message when asserting nothing was sent', function (): void {
            $proxy = new ClientProxyFake([new TestResponse, new TestResponse]);

            // Record multiple different requests to test the message concatenation
            $proxy->record(new TestRequest('servers', 'GET', []));
            $proxy->record(new TestRequest('volumes', 'POST', []));

            expect(fn () => $proxy->assertNothingSent())
                ->toThrow(ExpectationFailedException::class, 'The following requests were sent unexpectedly: servers, volumes');
        });
    });
});
