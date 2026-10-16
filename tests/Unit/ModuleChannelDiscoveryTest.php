<?php

declare(strict_types=1);
use App\Modules\Shared\Support\ModuleChannelDiscovery;

test('discovery prioritizes modules then appends sorted discovered channel files', function (): void {
    $basePath = $this->createTemporaryModuleBasePath('module-channel-discovery');

    $this->createChannelFile($basePath, 'Users');
    $this->createChannelFile($basePath, 'Shared');
    $this->createChannelFile($basePath, 'Billing');

    $discovered = ModuleChannelDiscovery::discover(
        basePath: $basePath,
        priorityModules: ['Shared', 'Users'],
    );

    expect($this->toRelativePaths($basePath, $discovered))->toBe([
        'app/Modules/Shared/Routes/channels.php',
        'app/Modules/Users/Routes/channels.php',
        'app/Modules/Billing/Routes/channels.php',
    ]);
});
test('discovery returns empty when modules directory is missing', function (): void {
    $basePath = $this->temporaryDirectoryPath('module-channel-discovery');

    $discovered = ModuleChannelDiscovery::discover($basePath);

    expect($discovered)->toBe([]);
});
