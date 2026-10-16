<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

final readonly class ModuleScaffoldPlan
{
    /**
     * @param  list<string>  $directories
     * @param  list<PlannedFile>  $files
     */
    public function __construct(
        public bool $moduleExists,
        public array $directories,
        public array $files,
    ) {}
}
