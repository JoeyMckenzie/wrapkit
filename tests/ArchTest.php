<?php

declare(strict_types=1);

namespace Tests;

arch('All source files are strictly typed')
    ->expect('HetznerCloud\\HttpClientUtilities\\')
    ->toUseStrictTypes();

arch('All tests files are strictly typed')
    ->expect('Tests\\')
    ->toUseStrictTypes();

arch('Value objects should be immutable')
    ->expect('HetznerCloud\\HttpClientUtilities\\ValueObjects\\')
    ->toBeFinal()
    ->and('HetznerCloud\\HttpClientUtilities\\ValueObjects\\')
    ->toBeReadonly();

arch('Contracts should be abstract')
    ->expect('HetznerCloud\\HttpClientUtilities\\Contracts\\')
    ->toBeInterfaces();

arch('All Enums are backed')
    ->expect('HetznerCloud\\HttpClientUtilities\\Enums\\')
    ->toBeStringBackedEnums();
