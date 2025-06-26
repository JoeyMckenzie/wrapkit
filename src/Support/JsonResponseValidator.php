<?php

declare(strict_types=1);

namespace Wrapkit\Support;

use Wrapkit\Contracts\ResponseValidatorContract;
use Wrapkit\Exceptions\UnserializableResponseException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

final class JsonResponseValidator implements ResponseValidatorContract
{
    public function validate(ResponseInterface $response, string $contents): void
    {
        try {
            $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

            // Ensure we got an array back
            if (! is_array($decoded)) {
                throw new UnserializableResponseException(
                    new JsonException('Response must decode to an array')
                );
            }
        } catch (JsonException $exception) {
            throw new UnserializableResponseException($exception);
        }
    }
}
