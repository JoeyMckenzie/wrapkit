<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Contracts;

use Psr\Http\Message\ResponseInterface;

interface ResponseValidatorContract
{
    public function validate(ResponseInterface $response, string $contents): void;

    public function shouldSkipValidation(ResponseInterface $response): bool;
}
