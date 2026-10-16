<?php

declare(strict_types=1);

use Spatie\TypeScriptTransformer\TypeScriptTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

test('the configured generator preserves nullable response properties', function (): void {
    $typeScriptTransformer = TypeScriptTransformer::create(app(TypeScriptTransformerConfig::class));
    [$types] = $typeScriptTransformer->resolveState();
    $files = $typeScriptTransformer->resolveFilesAction->execute($types);

    $output = implode("\n", array_column($files, 'contents'));

    expect($output)
        ->toMatch('/email_verified_at:\s*string\s*\|\s*null/')
        ->toMatch('/user:\s*UserViewData\s*\|\s*null/')
        ->toMatch('/flash:\s*SharedFlashData/')
        ->toMatch('/message:\s*string\s*\|\s*null/')
        ->toMatch('/appearance:\s*Appearance/')
        ->toMatch('/from:\s*number\s*\|\s*null/')
        ->toMatch('/targetUserId:\s*number\s*\|\s*null/');
});
