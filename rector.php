<?php

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use RectorLaravel\Set\LaravelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/app',
        __DIR__ . '/routes',
    ])

    ->withSets([
        LaravelSetList::LARAVEL_120,
    ])

    ->withPreparedSets(
        deadCode: true,
        codeQuality: true
    )
    ->withRules([
        TypedPropertyFromStrictConstructorRector::class
    ]);
