<?php

declare(strict_types=1);
use App\Modules\Shared\Support\ModuleRegistry;
use Illuminate\Contracts\Console\Kernel;

test('command writes cached module manifest', function (): void {
    $basePath = $this->createTemporaryModuleBasePath('module-cache-command');

    $this->createModuleFile($basePath, 'Marketing', 'Routes/web.php');
    $this->createModuleFile($basePath, 'Users', 'Routes/gates.php');
    $this->createModuleFile($basePath, 'Users', 'Policies/UserPolicy.php');
    $this->createModuleFile($basePath, 'Shared', 'Routes/channels.php');
    $this->createModuleDirectory($basePath, 'Users', 'Listeners');
    $this->createModuleFile($basePath, 'Users', 'Providers/ModuleServiceProvider.php');

    $exitCode = app(Kernel::class)->call('modules:cache', [
        '--base-path' => $basePath,
    ]);

    expect($exitCode)->toBe(0);

    $cachePath = ModuleRegistry::cachePath($basePath);

    expect($cachePath)->toBeFile();

    /** @var array<string, mixed> $payload */
    $payload = require $cachePath;
    $routes = $payload['routes'] ?? [];

    if (! is_array($routes)) {
        $routes = [];
    }

    expect($routes['web'] ?? [])->toBe([
        'app/Modules/Marketing/Routes/web.php',
    ]);

    expect($payload['gates'] ?? [])->toBe([
        'app/Modules/Users/Routes/gates.php',
    ]);

    expect($payload['policies'] ?? [])->toBe([
        'app/Modules/Users/Policies/UserPolicy.php',
    ]);

    expect($payload['channels'] ?? [])->toBe([
        'app/Modules/Shared/Routes/channels.php',
    ]);

    expect($payload['listeners'] ?? [])->toBe([
        'app/Modules/Users/Listeners',
    ]);

    expect($payload['providers'] ?? [])->toBe([
        'app/Modules/Users/Providers/ModuleServiceProvider.php',
    ]);
});
