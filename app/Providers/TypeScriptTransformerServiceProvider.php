<?php

declare(strict_types=1);

namespace App\Providers;

use Spatie\LaravelTypeScriptTransformer\LaravelData\LaravelDataTransformedProvider;
use Spatie\LaravelTypeScriptTransformer\LaravelData\Transformers\DataClassTransformer;
use Spatie\LaravelTypeScriptTransformer\LaravelTypeScriptTransformerExtension;
use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerApplicationServiceProvider;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;
use Spatie\TypeScriptTransformer\Transformers\AttributedClassTransformer;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\FlatModuleWriter;

/**
 * Backend-owned TypeScript contract generation.
 *
 * The whole frontend consumes these contracts from `@/types/app-data`, so the
 * output stays a single flat ES module. Route and controller generation is
 * intentionally left off: Wayfinder owns that surface.
 */
final class TypeScriptTransformerServiceProvider extends TypeScriptTransformerApplicationServiceProvider
{
    protected function configure(TypeScriptTransformerConfigFactory $config): void
    {
        $config
            // Registered before the Laravel extension so it can swap this
            // transformer for its Collection/EloquentCollection aware variant.
            ->transformer(AttributedClassTransformer::class)
            ->transformer(new EnumTransformer(useUnionEnums: false))
            ->extension(new LaravelTypeScriptTransformerExtension())
            // Data objects take precedence over the attribute transformer so
            // laravel-data name mapping, Lazy/Optional props, and nullable
            // properties are resolved with laravel-data semantics.
            ->prependTransformer(new DataClassTransformer())
            ->provider(LaravelDataTransformedProvider::class)
            ->transformDirectories(app_path())
            ->outputDirectory(resource_path('js/types'))
            ->writer(new FlatModuleWriter('app-data.ts'))
            ->formatter(PrettierFormatter::class)
            ->withoutManifest();
    }
}
