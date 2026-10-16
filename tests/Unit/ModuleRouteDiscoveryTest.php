<?php

declare(strict_types=1);
use App\Modules\Shared\Support\ModuleRouteDiscovery;

test('discovery prioritizes canonical web modules then appends sorted discovered modules', function (): void {
    $basePath = $this->createTemporaryModuleBasePath('module-route-discovery');

    $this->createRouteFile($basePath, 'Marketing', 'web');
    $this->createRouteFile($basePath, 'Auth', 'web');
    $this->createRouteFile($basePath, 'Users', 'web');
    $this->createRouteFile($basePath, 'Billing', 'web');

    $discovered = ModuleRouteDiscovery::discover(
        basePath: $basePath,
        routeType: 'web',
        priorityModules: ['Marketing', 'Auth', 'Users'],
    );

    expect($this->toRelativePaths($basePath, $discovered))->toBe([
        'app/Modules/Marketing/Routes/web.php',
        'app/Modules/Auth/Routes/web.php',
        'app/Modules/Users/Routes/web.php',
        'app/Modules/Billing/Routes/web.php',
    ]);
});
test('discovery prioritizes nested api modules with stable fallback order', function (): void {
    $basePath = $this->createTemporaryModuleBasePath('module-route-discovery');

    $this->createRouteFile($basePath, 'Api/V1', 'api');
    $this->createRouteFile($basePath, 'Api/V2', 'api');

    $discovered = ModuleRouteDiscovery::discover(
        basePath: $basePath,
        routeType: 'api',
        priorityModules: ['Api/V1'],
    );

    expect($this->toRelativePaths($basePath, $discovered))->toBe([
        'app/Modules/Api/V1/Routes/api.php',
        'app/Modules/Api/V2/Routes/api.php',
    ]);
});
test('discovery rejects invalid route types', function (): void {
    $basePath = $this->createTemporaryModuleBasePath('module-route-discovery');

    $this->expectException(InvalidArgumentException::class);
    ModuleRouteDiscovery::discover($basePath, 'graphql');
});
