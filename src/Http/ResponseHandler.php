<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Http;

use HetznerCloud\HttpClientUtilities\Contracts\ResponseHandlerContract;
use HetznerCloud\HttpClientUtilities\Contracts\ResponseValidatorContract;
use HetznerCloud\HttpClientUtilities\Exceptions\UnserializableResponseException;
use HetznerCloud\HttpClientUtilities\ValueObjects\Connector\Response;
use JsonException;
use Psr\Http\Message\ResponseInterface;

final readonly class ResponseHandler implements ResponseHandlerContract
{
    public function __construct(
        private ResponseValidatorContract $validator
    ) {}

    /**
     * @return Response<array<array-key, mixed>>|null
     *
     * @throws UnserializableResponseException
     */
    public function handle(ResponseInterface $response, bool $skipResponse): ?Response
    {
        if ($skipResponse) {
            return null;
        }

        $contents = $response->getBody()->getContents();
        $this->validator->validate($response, $contents);

        try {
            /** @var array<array-key, mixed> $data */
            $data = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new UnserializableResponseException($exception);
        }

        return Response::from($data);
    }
}
