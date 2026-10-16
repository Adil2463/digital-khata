<?php

declare(strict_types=1);

use App\Modules\Shared\Support\ModuleRegistry;

$moduleRouteFiles = ModuleRegistry::webRoutes(base_path());

foreach ($moduleRouteFiles as $moduleRouteFile) {
    require $moduleRouteFile;
}
