<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support;

use InvalidArgumentException;

final class ModuleRouteDiscovery
{
    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    public static function discover(string $basePath, string $routeType, array $priorityModules = []): array
    {
        $normalizedRouteType = mb_strtolower(mb_trim($routeType));

        if (! in_array($normalizedRouteType, ['web', 'api'], true)) {
            throw new InvalidArgumentException('Route type must be either "web" or "api".');
        }

        return ModuleRegistry::routeFiles($basePath, $normalizedRouteType, $priorityModules);
    }
}
