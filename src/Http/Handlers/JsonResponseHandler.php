<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Http\Handlers;

use HetznerCloud\HttpClientUtilities\Contracts\ResponseHandlerContract;
use HetznerCloud\HttpClientUtilities\Exceptions\UnserializableResponseException;
use HetznerCloud\HttpClientUtilities\ValueObjects\Response;
use JsonException;
use Psr\Http\Message\ResponseInterface;

final readonly class JsonResponseHandler implements ResponseHandlerContract
{
    /**
     * @return Response<array<array-key, mixed>>
     *
     * @throws UnserializableResponseException
     */
    public function handle(ResponseInterface $response): Response
    {
        $contents = $response->getBody()->getContents();

        try {
            $data = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

            // Ensure we got an array back
            if (! is_array($data)) {
                throw new UnserializableResponseException(
                    new JsonException('Response must decode to an array')
                );
            }
        } catch (JsonException $exception) {
            throw new UnserializableResponseException($exception);
        }

        return Response::from($data);
    }
}
