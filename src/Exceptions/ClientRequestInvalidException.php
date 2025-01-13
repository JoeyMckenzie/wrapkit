<?php

declare(strict_types=1);

namespace HetznerCloud\HttpClientUtilities\Exceptions;

use Exception;

final class ClientRequestInvalidException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
