<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/bootstrap/app.php',
        __DIR__.'/bootstrap/providers.php',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/public',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ])
    ->withImportNames(
        removeUnusedImports: true,
    )
    ->withComposerBased(
        phpunit: true,
        laravel: true,
    )
    ->withCache(
        cacheDirectory: __DIR__.'/storage/framework/cache/rector',
        cacheClass: FileCacheStorage::class,
    )
    ->withSkip([
        __DIR__.'/config/database.php',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        typeDeclarationDocblocks: true,
        privatization: true,
        naming: true,
        instanceOf: true,
        earlyReturn: true,
        phpunitCodeQuality: true,
        phpunitNarrowAsserts: true,
    )
    ->withPhpSets();
