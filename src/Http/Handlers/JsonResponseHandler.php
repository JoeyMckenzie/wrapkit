<?php

declare(strict_types=1);

namespace Wrapkit\Http\Handlers;

use Wrapkit\Contracts\ResponseHandlerContract;
use Wrapkit\Exceptions\UnserializableResponseException;
use Wrapkit\ValueObjects\Response;
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
