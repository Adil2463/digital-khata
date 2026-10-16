<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

final readonly class PlannedFile
{
    public function __construct(
        public string $path,
        public string $contents,
    ) {}
}
