<?php

declare(strict_types=1);
use App\Modules\Shared\Support\ModuleGateDiscovery;

test('discovery prioritizes modules then appends sorted discovered gate files', function (): void {
    $basePath = $this->createTemporaryModuleBasePath('module-gate-discovery');

    $this->createGateFile($basePath, 'Users');
    $this->createGateFile($basePath, 'Billing');
    $this->createGateFile($basePath, 'Api/V1');

    $discovered = ModuleGateDiscovery::discover(
        basePath: $basePath,
        priorityModules: ['Users'],
    );

    expect($this->toRelativePaths($basePath, $discovered))->toBe([
        'app/Modules/Users/Routes/gates.php',
        'app/Modules/Api/V1/Routes/gates.php',
        'app/Modules/Billing/Routes/gates.php',
    ]);
});
test('discovery returns empty when modules directory is missing', function (): void {
    $basePath = $this->temporaryDirectoryPath('module-gate-discovery');

    $discovered = ModuleGateDiscovery::discover($basePath);

    expect($discovered)->toBe([]);
});
