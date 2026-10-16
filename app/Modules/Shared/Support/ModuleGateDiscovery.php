<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support;

final class ModuleGateDiscovery
{
    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    public static function discover(string $basePath, array $priorityModules = []): array
    {
        return ModuleRegistry::gateFilesWithPriority($basePath, $priorityModules);
    }
}
