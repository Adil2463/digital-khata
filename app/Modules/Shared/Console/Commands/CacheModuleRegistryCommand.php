<?php

declare(strict_types=1);

namespace App\Modules\Shared\Console\Commands;

use App\Modules\Shared\Support\ModuleRegistry;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

final class CacheModuleRegistryCommand extends Command
{
    protected $signature = 'modules:cache
        {--base-path= : Override base path (testing only)}';

    protected $description = 'Cache the module discovery manifest.';

    public function __construct(
        private readonly Filesystem $filesystem,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $basePathOption = $this->input()->string('base-path')->toString();
        $basePath = mb_trim(
            $basePathOption !== ''
                ? $basePathOption
                : base_path(),
        );
        $cachePath = ModuleRegistry::cachePath($basePath);
        $cacheDirectory = dirname($cachePath);

        if (! $this->filesystem->isDirectory($cacheDirectory)) {
            $this->filesystem->makeDirectory($cacheDirectory, 0755, true);
        }

        $this->filesystem->put($cachePath, ModuleRegistry::cacheFileContents($basePath));

        $payload = ModuleRegistry::buildCachePayload($basePath);
        $entryCount = count($payload['routes']['web'])
            + count($payload['routes']['api'])
            + count($payload['gates'])
            + count($payload['policies'])
            + count($payload['channels'])
            + count($payload['listeners'])
            + count($payload['providers']);

        $this->info(sprintf(
            'Cached module registry manifest with %d entries at [%s].',
            $entryCount,
            $cachePath,
        ));

        return self::SUCCESS;
    }
}
