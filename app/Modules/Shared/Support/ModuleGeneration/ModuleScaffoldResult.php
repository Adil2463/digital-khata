<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

final readonly class ModuleScaffoldResult
{
    /**
     * @param  list<string>  $directories
     * @param  list<string>  $files
     * @param  list<string>  $overwrittenFiles
     */
    public function __construct(
        public bool $dryRun,
        public array $directories,
        public array $files,
        public array $overwrittenFiles,
    ) {}
}
