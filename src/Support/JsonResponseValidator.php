<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Support;

use HetznerCloud\HttpClientUtilities\Contracts\ResponseValidatorContract;
use HetznerCloud\HttpClientUtilities\Enums\MediaType;
use HetznerCloud\HttpClientUtilities\Exceptions\UnserializableResponseException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

final class JsonResponseValidator implements ResponseValidatorContract
{
    public function validate(ResponseInterface $response, string $contents): void
    {
        if ($this->shouldSkipValidation($response)) {
            return;
        }

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

    public function shouldSkipValidation(ResponseInterface $response): bool
    {
        // Explicitly check against 400 to avoid mutation
        $statusCode = $response->getStatusCode();
        if ($statusCode < 400) {
            return true;
        }

        return ! str_contains($response->getHeaderLine('Content-Type'), MediaType::JSON->value);
    }
}
