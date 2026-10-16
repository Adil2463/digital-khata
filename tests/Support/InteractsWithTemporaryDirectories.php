<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Tests\TestCase;

/** @mixin TestCase */
trait InteractsWithTemporaryDirectories
{
    /**
     * @var list<string>
     */
    private array $temporaryDirectories = [];

    protected function temporaryDirectoryPath(string $prefix): string
    {
        $path = storage_path('framework/testing/'.$prefix.'-'.Str::uuid()->toString());
        $this->temporaryDirectories[] = $path;

        return $path;
    }

    protected function ensureDirectory(string $path): void
    {
        $filesystem = app(Filesystem::class);

        if (! $filesystem->isDirectory($path)) {
            $filesystem->makeDirectory($path, 0755, true);
        }
    }

    protected function tearDownInteractsWithTemporaryDirectories(): void
    {
        if ($this->temporaryDirectories === []) {
            return;
        }

        $filesystem = app(Filesystem::class);

        foreach ($this->temporaryDirectories as $temporaryDirectory) {
            if ($filesystem->isDirectory($temporaryDirectory)) {
                $filesystem->deleteDirectory($temporaryDirectory);
            }
        }

        $this->temporaryDirectories = [];
    }
}
