<?php

declare(strict_types=1);

namespace Wrapkit\Contracts;

use Psr\Http\Message\ResponseInterface;

interface ResponseValidatorContract
{
    public function validate(ResponseInterface $response, string $contents): void;
}
