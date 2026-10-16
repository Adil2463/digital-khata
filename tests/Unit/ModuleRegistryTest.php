<?php

declare(strict_types=1);
use App\Modules\Shared\Providers\ModuleServiceProvider;
use App\Modules\Shared\Support\ModuleRegistry;

afterEach(function (): void {
    ModuleRegistry::flushRuntimeCache();
});
test('registry discovers and prioritizes all supported module assets', function (): void {
    $basePath = $this->createTemporaryModuleBasePath('module-registry', withBootstrapCache: true);

    $this->createModuleFile($basePath, 'Marketing', 'Routes/web.php');
    $this->createModuleFile($basePath, 'Auth', 'Routes/web.php');
    $this->createModuleFile($basePath, 'Users', 'Routes/web.php');
    $this->createModuleFile($basePath, 'Billing', 'Routes/web.php');
    $this->createModuleFile($basePath, 'Api/V1', 'Routes/api.php');
    $this->createModuleFile($basePath, 'Api/V2', 'Routes/api.php');
    $this->createModuleFile($basePath, 'Users', 'Routes/gates.php');
    $this->createModuleFile($basePath, 'Billing', 'Routes/gates.php');
    $this->createModuleFile($basePath, 'Users', 'Policies/UserPolicy.php');
    $this->createModuleFile($basePath, 'Billing', 'Policies/BillingPolicy.php');
    $this->createModuleFile($basePath, 'Shared', 'Routes/channels.php');
    $this->createModuleFile($basePath, 'Users', 'Routes/channels.php');
    $this->createModuleFile($basePath, 'Billing', 'Routes/channels.php');
    $this->createModuleDirectory($basePath, 'Users', 'Listeners');
    $this->createModuleDirectory($basePath, 'Billing', 'Listeners');
    $this->createModuleDirectory($basePath, 'Api/V1', 'Listeners');
    $this->createModuleFile($basePath, 'Users', 'Providers/ModuleServiceProvider.php');
    $this->createModuleFile($basePath, 'Billing', 'Providers/ModuleServiceProvider.php');

    expect($this->toRelativePaths($basePath, ModuleRegistry::webRoutes($basePath)))->toBe([
        'app/Modules/Marketing/Routes/web.php',
        'app/Modules/Auth/Routes/web.php',
        'app/Modules/Users/Routes/web.php',
        'app/Modules/Billing/Routes/web.php',
    ]);

    expect($this->toRelativePaths($basePath, ModuleRegistry::apiRoutes($basePath)))->toBe([
        'app/Modules/Api/V1/Routes/api.php',
        'app/Modules/Api/V2/Routes/api.php',
    ]);

    expect($this->toRelativePaths($basePath, ModuleRegistry::gateFiles($basePath)))->toBe([
        'app/Modules/Users/Routes/gates.php',
        'app/Modules/Billing/Routes/gates.php',
    ]);

    expect($this->toRelativePaths($basePath, ModuleRegistry::policyFiles($basePath)))->toBe([
        'app/Modules/Users/Policies/UserPolicy.php',
        'app/Modules/Billing/Policies/BillingPolicy.php',
    ]);

    expect($this->toRelativePaths($basePath, ModuleRegistry::channelFiles($basePath)))->toBe([
        'app/Modules/Shared/Routes/channels.php',
        'app/Modules/Users/Routes/channels.php',
        'app/Modules/Billing/Routes/channels.php',
    ]);

    expect($this->toRelativePaths($basePath, ModuleRegistry::listenerDirectories($basePath)))->toBe([
        'app/Modules/Users/Listeners',
        'app/Modules/Api/V1/Listeners',
        'app/Modules/Billing/Listeners',
    ]);

    expect($this->toRelativePaths($basePath, ModuleRegistry::providerFiles($basePath)))->toBe([
        'app/Modules/Billing/Providers/ModuleServiceProvider.php',
        'app/Modules/Users/Providers/ModuleServiceProvider.php',
    ]);

    expect(ModuleRegistry::providerClasses($basePath))->toBe([]);
});
test('registry uses cached manifest when available', function (): void {
    $basePath = $this->createTemporaryModuleBasePath('module-registry', withBootstrapCache: true);

    $this->createModuleFile($basePath, 'Cached', 'Routes/web.php');
    $this->createModuleFile($basePath, 'Cached', 'Routes/gates.php');
    $this->createModuleFile($basePath, 'Cached', 'Policies/CachedPolicy.php');
    $this->createModuleDirectory($basePath, 'Cached', 'Listeners');
    $this->createModuleFile($basePath, 'Ignored', 'Routes/web.php');

    file_put_contents(
        ModuleRegistry::cachePath($basePath),
        <<<'PHP'
<?php

return [
    'version' => 3,
    'routes' => [
        'web' => ['app/Modules/Cached/Routes/web.php'],
        'api' => [],
    ],
    'gates' => ['app/Modules/Cached/Routes/gates.php'],
    'policies' => ['app/Modules/Cached/Policies/CachedPolicy.php'],
    'channels' => [],
    'listeners' => ['app/Modules/Cached/Listeners'],
    'providers' => [],
];
PHP
    );

    expect($this->toRelativePaths($basePath, ModuleRegistry::webRoutes($basePath)))->toBe([
        'app/Modules/Cached/Routes/web.php',
    ]);

    expect($this->toRelativePaths($basePath, ModuleRegistry::gateFiles($basePath)))->toBe([
        'app/Modules/Cached/Routes/gates.php',
    ]);

    expect($this->toRelativePaths($basePath, ModuleRegistry::policyFiles($basePath)))->toBe([
        'app/Modules/Cached/Policies/CachedPolicy.php',
    ]);

    expect($this->toRelativePaths($basePath, ModuleRegistry::listenerDirectories($basePath)))->toBe([
        'app/Modules/Cached/Listeners',
    ]);
});
test('registry rebuilds when cached manifest references deleted entries', function (): void {
    $basePath = $this->createTemporaryModuleBasePath('module-registry', withBootstrapCache: true);

    $this->createModuleFile($basePath, 'Billing', 'Routes/web.php');
    $this->createModuleFile($basePath, 'Billing', 'Routes/gates.php');
    $this->createModuleDirectory($basePath, 'Billing', 'Listeners');

    file_put_contents(
        ModuleRegistry::cachePath($basePath),
        <<<'PHP'
<?php

return [
    'version' => 3,
    'routes' => [
        'web' => ['app/Modules/Application/Routes/web.php'],
        'api' => [],
    ],
    'gates' => ['app/Modules/Application/Routes/gates.php'],
    'policies' => ['app/Modules/Application/Policies/ApplicationPolicy.php'],
    'channels' => [],
    'listeners' => ['app/Modules/Application/Listeners'],
    'providers' => [],
];
PHP
    );

    expect($this->toRelativePaths($basePath, ModuleRegistry::webRoutes($basePath)))->toBe([
        'app/Modules/Billing/Routes/web.php',
    ]);

    expect($this->toRelativePaths($basePath, ModuleRegistry::gateFiles($basePath)))->toBe([
        'app/Modules/Billing/Routes/gates.php',
    ]);

    expect(ModuleRegistry::policyFiles($basePath))->toBe([]);

    expect($this->toRelativePaths($basePath, ModuleRegistry::listenerDirectories($basePath)))->toBe([
        'app/Modules/Billing/Listeners',
    ]);
});
test('registry resolves autoloadable provider classes for the application', function (): void {
    expect(ModuleRegistry::providerClasses(base_path()))->toContain(ModuleServiceProvider::class);
});
test('registry runtime caches discovery results until flushed', function (): void {
    $basePath = $this->createTemporaryModuleBasePath('module-registry', withBootstrapCache: true);

    $this->createModuleFile($basePath, 'Billing', 'Routes/web.php');

    expect($this->toRelativePaths($basePath, ModuleRegistry::webRoutes($basePath)))->toBe([
        'app/Modules/Billing/Routes/web.php',
    ]);

    $this->createModuleFile($basePath, 'Reports', 'Routes/web.php');

    expect($this->toRelativePaths($basePath, ModuleRegistry::webRoutes($basePath)))->toBe([
        'app/Modules/Billing/Routes/web.php',
    ]);

    ModuleRegistry::flushRuntimeCache();

    expect($this->toRelativePaths($basePath, ModuleRegistry::webRoutes($basePath)))->toBe([
        'app/Modules/Billing/Routes/web.php',
        'app/Modules/Reports/Routes/web.php',
    ]);
});
test('registry trusts cached manifest for production http runtime', function (): void {
    $basePath = $this->createTemporaryModuleBasePath('module-registry', withBootstrapCache: true);

    $this->createModuleFile($basePath, 'Billing', 'Routes/web.php');

    file_put_contents(
        ModuleRegistry::cachePath($basePath),
        <<<'PHP'
<?php

return [
    'version' => 3,
    'routes' => [
        'web' => ['app/Modules/Cached/Routes/web.php'],
        'api' => [],
    ],
    'gates' => [],
    'policies' => [],
    'channels' => [],
    'listeners' => [],
    'providers' => [],
];
PHP
    );

    expect($this->toRelativePaths($basePath, ModuleRegistry::discover($basePath, false, 'production')['routes']['web']))->toBe([
        'app/Modules/Cached/Routes/web.php',
    ]);

    ModuleRegistry::flushRuntimeCache();

    expect($this->toRelativePaths($basePath, ModuleRegistry::discover($basePath, true, 'production')['routes']['web']))->toBe([
        'app/Modules/Billing/Routes/web.php',
    ]);
});
