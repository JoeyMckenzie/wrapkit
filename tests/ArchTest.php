<?php

declare(strict_types=1);

namespace Tests;

arch('All source files are strictly typed')
    ->expect('Wrapkit\\')
    ->toUseStrictTypes();

arch('All tests files are strictly typed')
    ->expect('Tests\\')
    ->toUseStrictTypes();

arch('Value objects should be immutable')
    ->expect('Wrapkit\\ValueObjects\\')
    ->toBeFinal()
    ->and('Wrapkit\\ValueObjects\\')
    ->toBeReadonly();

arch('Contracts should be abstract')
    ->expect('Wrapkit\\Contracts\\')
    ->toBeInterfaces();

arch('All Enums are backed')
    ->expect('Wrapkit\\Enums\\')
    ->toBeStringBackedEnums();
