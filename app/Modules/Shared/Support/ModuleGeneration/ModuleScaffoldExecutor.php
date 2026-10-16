<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;

final readonly class ModuleScaffoldExecutor
{
    public function __construct(
        private Filesystem $filesystem,
    ) {}

    public function execute(ModuleScaffoldPlan $moduleScaffoldPlan, bool $force, bool $dryRun): ModuleScaffoldResult
    {
        $existingFiles = [];

        foreach ($moduleScaffoldPlan->files as $plannedFile) {
            if ($this->filesystem->exists($plannedFile->path)) {
                $existingFiles[] = $plannedFile->path;
            }
        }

        if ($existingFiles !== [] && ! $force) {
            throw new RuntimeException(
                "Scaffolding aborted because target files already exist:\n- ".implode("\n- ", $existingFiles)."\nUse --force to overwrite them.",
            );
        }

        $filePaths = array_map(
            static fn (PlannedFile $plannedFile): string => $plannedFile->path,
            $moduleScaffoldPlan->files,
        );

        if ($dryRun) {
            return new ModuleScaffoldResult(
                dryRun: true,
                directories: $moduleScaffoldPlan->directories,
                files: $filePaths,
                overwrittenFiles: $existingFiles,
            );
        }

        foreach ($moduleScaffoldPlan->directories as $directory) {
            if (! $this->filesystem->isDirectory($directory)) {
                $this->filesystem->makeDirectory($directory, 0755, true);
            }
        }

        foreach ($moduleScaffoldPlan->files as $plannedFile) {
            $contents = mb_rtrim($plannedFile->contents)."\n";

            $this->filesystem->put($plannedFile->path, $contents);
        }

        return new ModuleScaffoldResult(
            dryRun: false,
            directories: $moduleScaffoldPlan->directories,
            files: $filePaths,
            overwrittenFiles: $existingFiles,
        );
    }
}
