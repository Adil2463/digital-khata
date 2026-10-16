<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

/** @mixin TestCase */
trait InteractsWithModuleFixtures
{
    protected function createTemporaryModuleBasePath(string $prefix, bool $withBootstrapCache = false): string
    {
        $basePath = $this->temporaryDirectoryPath($prefix);

        $this->ensureDirectory($basePath.'/app/Modules');

        if ($withBootstrapCache) {
            $this->ensureDirectory($basePath.'/bootstrap/cache');
        }

        return $basePath;
    }

    protected function createModuleDirectory(string $basePath, string $modulePath, string $relativePath): void
    {
        $path = $basePath.'/app/Modules/'.str_replace('\\', '/', $modulePath).'/'.$relativePath;

        $this->ensureDirectory($path);
    }

    protected function createModuleFile(string $basePath, string $modulePath, string $relativePath): void
    {
        $filesystem = app(Filesystem::class);
        $path = $basePath.'/app/Modules/'.str_replace('\\', '/', $modulePath).'/'.$relativePath;

        $this->ensureDirectory(dirname($path));
        $filesystem->put($path, '<?php');
    }

    protected function createRouteFile(string $basePath, string $modulePath, string $routeType): void
    {
        $this->createModuleFile($basePath, $modulePath, 'Routes/'.$routeType.'.php');
    }

    protected function createChannelFile(string $basePath, string $modulePath): void
    {
        $this->createModuleFile($basePath, $modulePath, 'Routes/channels.php');
    }

    protected function createGateFile(string $basePath, string $modulePath): void
    {
        $this->createModuleFile($basePath, $modulePath, 'Routes/gates.php');
    }

    protected function createListenerDirectory(string $basePath, string $modulePath): void
    {
        $this->createModuleDirectory($basePath, $modulePath, 'Listeners');
    }

    /**
     * @param  list<string>  $absolutePaths
     * @return list<string>
     */
    protected function toRelativePaths(string $basePath, array $absolutePaths): array
    {
        $normalizedBasePath = str_replace('\\', '/', mb_rtrim($basePath, '\\/'));

        return array_map(
            static fn (string $absolutePath): string => mb_ltrim(str_replace(
                $normalizedBasePath,
                '',
                str_replace('\\', '/', $absolutePath),
            ), '/'),
            $absolutePaths,
        );
    }
}
