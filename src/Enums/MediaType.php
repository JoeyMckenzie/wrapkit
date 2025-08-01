<?php

declare(strict_types=1);

namespace Wrapkit\Enums;

/**
 * Represents various media types to be used in headers for expected/received responses.
 */
enum MediaType: string
{
    case JSON = 'application/json';

    case MULTIPART = 'multipart/form-data';

    case FORM = 'application/x-www-form-urlencoded';
}
