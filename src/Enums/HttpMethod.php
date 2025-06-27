<?php

declare(strict_types=1);

namespace Wrapkit\Enums;

/**
 * Represents various HTTP methods utilized for sending requests.
 */
enum HttpMethod: string
{
    case GET = 'GET';

    case POST = 'POST';

    case PUT = 'PUT';

    case PATCH = 'PATCH';

    case DELETE = 'DELETE';
}
