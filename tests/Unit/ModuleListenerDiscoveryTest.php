<?php

declare(strict_types=1);
use App\Modules\Shared\Support\ModuleListenerDiscovery;

test('discovery prioritizes modules then appends sorted listener directories', function (): void {
    $basePath = $this->createTemporaryModuleBasePath('module-listener-discovery');

    $this->createListenerDirectory($basePath, 'Users');
    $this->createListenerDirectory($basePath, 'Billing');
    $this->createListenerDirectory($basePath, 'Api/V1');

    $discovered = ModuleListenerDiscovery::discoverDirectories(
        basePath: $basePath,
        priorityModules: ['Users'],
    );

    expect($this->toRelativePaths($basePath, $discovered))->toBe([
        'app/Modules/Users/Listeners',
        'app/Modules/Api/V1/Listeners',
        'app/Modules/Billing/Listeners',
    ]);
});
test('discovery returns empty when modules directory is missing', function (): void {
    $basePath = $this->temporaryDirectoryPath('module-listener-discovery');

    $discovered = ModuleListenerDiscovery::discoverDirectories($basePath);

    expect($discovered)->toBe([]);
});
