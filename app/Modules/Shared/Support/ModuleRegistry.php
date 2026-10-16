<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support;

use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class ModuleRegistry
{
    private const int CACHE_VERSION = 3;

    private const string CACHE_RELATIVE_PATH = 'bootstrap/cache/modules.php';

    /**
     * @var array{
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     policies: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    private const array DEFAULT_PRIORITY_MODULES = [
        'routes' => [
            'web' => ['Marketing', 'Auth', 'Dashboard', 'Settings', 'Users'],
            'api' => ['Api/V1'],
        ],
        'gates' => ['Users'],
        'policies' => ['Users'],
        'channels' => ['Shared', 'Users'],
        'listeners' => ['Users'],
        'providers' => [],
    ];

    /**
     * @var array<string, array{
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     policies: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }>
     */
    private static array $resolvedPayloadCache = [];

    /**
     * @return list<string>
     */
    public static function webRoutes(string $basePath): array
    {
        /** @var list<string> $entries */
        $entries = self::discover($basePath)['routes']['web'];

        return $entries;
    }

    /**
     * @return list<string>
     */
    public static function apiRoutes(string $basePath): array
    {
        /** @var list<string> $entries */
        $entries = self::discover($basePath)['routes']['api'];

        return $entries;
    }

    /**
     * @param  list<string>|null  $priorityModules
     * @return list<string>
     */
    public static function routeFiles(string $basePath, string $routeType, ?array $priorityModules = null): array
    {
        $normalizedRouteType = self::normalizeRouteType($routeType);

        if ($priorityModules === null) {
            return $normalizedRouteType === 'web'
                ? self::webRoutes($basePath)
                : self::apiRoutes($basePath);
        }

        $modulesRoot = self::modulesRoot($basePath);

        if (! is_dir($modulesRoot)) {
            return [];
        }

        return self::resolveEntries(
            $basePath,
            self::scanRouteEntries(
                modulesRoot: $modulesRoot,
                basePath: self::normalizeBasePath($basePath),
                routeType: $normalizedRouteType,
                priorityModules: $priorityModules,
            ),
        );
    }

    /**
     * @return list<string>
     */
    public static function gateFiles(string $basePath): array
    {
        /** @var list<string> $entries */
        $entries = self::discover($basePath)['gates'];

        return $entries;
    }

    /**
     * @return list<string>
     */
    public static function policyFiles(string $basePath): array
    {
        /** @var list<string> $entries */
        $entries = self::discover($basePath)['policies'];

        return $entries;
    }

    /**
     * @return array<class-string, class-string>
     */
    public static function policyMap(string $basePath): array
    {
        $policyMap = [];

        foreach (self::policyFiles($basePath) as $policyFile) {
            $policyClass = self::policyClassFromPath($basePath, $policyFile);

            if ($policyClass === null) {
                continue;
            }

            $modelClass = self::modelClassFromPolicyClass($policyClass);

            if ($modelClass === null) {
                continue;
            }

            $policyMap[$modelClass] = $policyClass;
        }

        return $policyMap;
    }

    /**
     * @param  list<string>|null  $priorityModules
     * @return list<string>
     */
    public static function gateFilesWithPriority(string $basePath, ?array $priorityModules = null): array
    {
        if ($priorityModules === null) {
            return self::gateFiles($basePath);
        }

        $modulesRoot = self::modulesRoot($basePath);

        if (! is_dir($modulesRoot)) {
            return [];
        }

        return self::resolveEntries(
            $basePath,
            self::scanRouteSupportEntries(
                modulesRoot: $modulesRoot,
                basePath: self::normalizeBasePath($basePath),
                filename: 'gates.php',
                priorityModules: $priorityModules,
            ),
        );
    }

    /**
     * @return list<string>
     */
    public static function channelFiles(string $basePath): array
    {
        /** @var list<string> $entries */
        $entries = self::discover($basePath)['channels'];

        return $entries;
    }

    /**
     * @param  list<string>|null  $priorityModules
     * @return list<string>
     */
    public static function channelFilesWithPriority(string $basePath, ?array $priorityModules = null): array
    {
        if ($priorityModules === null) {
            return self::channelFiles($basePath);
        }

        $modulesRoot = self::modulesRoot($basePath);

        if (! is_dir($modulesRoot)) {
            return [];
        }

        return self::resolveEntries(
            $basePath,
            self::scanRouteSupportEntries(
                modulesRoot: $modulesRoot,
                basePath: self::normalizeBasePath($basePath),
                filename: 'channels.php',
                priorityModules: $priorityModules,
            ),
        );
    }

    /**
     * @return list<string>
     */
    public static function listenerDirectories(string $basePath): array
    {
        /** @var list<string> $entries */
        $entries = self::discover($basePath)['listeners'];

        return $entries;
    }

    /**
     * @param  list<string>|null  $priorityModules
     * @return list<string>
     */
    public static function listenerDirectoriesWithPriority(string $basePath, ?array $priorityModules = null): array
    {
        if ($priorityModules === null) {
            return self::listenerDirectories($basePath);
        }

        $modulesRoot = self::modulesRoot($basePath);

        if (! is_dir($modulesRoot)) {
            return [];
        }

        return self::resolveEntries(
            $basePath,
            self::scanNamedDirectoryEntries(
                modulesRoot: $modulesRoot,
                basePath: self::normalizeBasePath($basePath),
                directoryName: 'Listeners',
                priorityModules: $priorityModules,
            ),
        );
    }

    /**
     * @return list<string>
     */
    public static function providerFiles(string $basePath): array
    {
        /** @var list<string> $entries */
        $entries = self::discover($basePath)['providers'];

        return $entries;
    }

    /**
     * @return list<class-string<ServiceProvider>>
     */
    public static function providerClasses(string $basePath): array
    {
        $classes = array_values(array_filter(
            array_map(
                static fn (string $providerFile): ?string => self::providerClassFromPath($basePath, $providerFile),
                self::providerFiles($basePath),
            ),
            static fn (?string $providerClass): bool => $providerClass !== null,
        ));

        return array_values(array_unique($classes));
    }

    /**
     * @return array{
     *     version: int,
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     policies: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    public static function buildCachePayload(string $basePath): array
    {
        return self::scanPayload(self::normalizeBasePath($basePath));
    }

    public static function cachePath(string $basePath): string
    {
        return self::normalizeBasePath($basePath).DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, self::CACHE_RELATIVE_PATH);
    }

    public static function cacheFileContents(string $basePath): string
    {
        return "<?php\n\nreturn ".var_export(self::buildCachePayload($basePath), true).";\n";
    }

    public static function flushRuntimeCache(): void
    {
        self::$resolvedPayloadCache = [];
    }

    public static function shouldTrustCachedManifest(?bool $runningInConsole = null, ?string $appEnv = null): bool
    {
        $resolvedRunningInConsole = $runningInConsole ?? self::runningInConsole();
        $resolvedAppEnv = mb_strtolower(mb_trim($appEnv ?? self::appEnv()));

        return $resolvedAppEnv === 'production' && ! $resolvedRunningInConsole;
    }

    /**
     * @return array{
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     policies: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    public static function discover(string $basePath, ?bool $runningInConsole = null, ?string $appEnv = null): array
    {
        $normalizedBasePath = self::normalizeBasePath($basePath);

        if (isset(self::$resolvedPayloadCache[$normalizedBasePath])) {
            return self::$resolvedPayloadCache[$normalizedBasePath];
        }

        $cachedPayload = self::loadCachedPayload($normalizedBasePath);
        $payload = match (true) {
            $cachedPayload === null => self::scanPayload($normalizedBasePath),
            self::shouldTrustCachedManifest($runningInConsole, $appEnv) => $cachedPayload,
            ! self::payloadHasMissingEntries($normalizedBasePath, $cachedPayload) => $cachedPayload,
            default => self::scanPayload($normalizedBasePath),
        };

        return self::$resolvedPayloadCache[$normalizedBasePath] = self::resolvePayloadEntries($normalizedBasePath, $payload);
    }

    private static function normalizeRouteType(string $routeType): string
    {
        $normalizedRouteType = mb_strtolower(mb_trim($routeType));

        if (! in_array($normalizedRouteType, ['web', 'api'], true)) {
            throw new InvalidArgumentException('Route type must be either "web" or "api".');
        }

        return $normalizedRouteType;
    }

    private static function normalizeBasePath(string $basePath): string
    {
        return mb_rtrim($basePath, '\\/');
    }

    private static function appEnv(): string
    {
        $appEnv = getenv('APP_ENV');

        if (is_string($appEnv) && $appEnv !== '') {
            return $appEnv;
        }

        $serverAppEnv = $_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? 'production';

        return is_string($serverAppEnv) && $serverAppEnv !== ''
            ? $serverAppEnv
            : 'production';
    }

    private static function runningInConsole(): bool
    {
        return in_array(PHP_SAPI, ['cli', 'phpdbg'], true);
    }

    private static function modulesRoot(string $basePath): string
    {
        return self::normalizeBasePath($basePath).DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Modules';
    }

    /**
     * @return array{
     *     version: int,
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     policies: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    private static function scanPayload(string $basePath): array
    {
        $modulesRoot = self::modulesRoot($basePath);

        if (! is_dir($modulesRoot)) {
            return self::emptyPayload();
        }

        return [
            'version' => self::CACHE_VERSION,
            'routes' => [
                'web' => self::scanRouteEntries(
                    modulesRoot: $modulesRoot,
                    basePath: $basePath,
                    routeType: 'web',
                    priorityModules: self::DEFAULT_PRIORITY_MODULES['routes']['web'],
                ),
                'api' => self::scanRouteEntries(
                    modulesRoot: $modulesRoot,
                    basePath: $basePath,
                    routeType: 'api',
                    priorityModules: self::DEFAULT_PRIORITY_MODULES['routes']['api'],
                ),
            ],
            'gates' => self::scanRouteSupportEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                filename: 'gates.php',
                priorityModules: self::DEFAULT_PRIORITY_MODULES['gates'],
            ),
            'policies' => self::scanPolicyEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                priorityModules: self::DEFAULT_PRIORITY_MODULES['policies'],
            ),
            'channels' => self::scanRouteSupportEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                filename: 'channels.php',
                priorityModules: self::DEFAULT_PRIORITY_MODULES['channels'],
            ),
            'listeners' => self::scanNamedDirectoryEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: 'Listeners',
                priorityModules: self::DEFAULT_PRIORITY_MODULES['listeners'],
            ),
            'providers' => self::scanNamedFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: 'Providers',
                filename: 'ModuleServiceProvider.php',
                priorityModules: self::DEFAULT_PRIORITY_MODULES['providers'],
            ),
        ];
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function scanRouteEntries(string $modulesRoot, string $basePath, string $routeType, array $priorityModules): array
    {
        $filename = self::normalizeRouteType($routeType).'.php';

        return self::mergePrioritizedEntries(
            priorityEntries: self::priorityFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: 'Routes',
                filename: $filename,
                priorityModules: $priorityModules,
            ),
            discoveredEntries: self::discoverNamedFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: 'Routes',
                filename: $filename,
            ),
        );
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function scanRouteSupportEntries(string $modulesRoot, string $basePath, string $filename, array $priorityModules): array
    {
        return self::mergePrioritizedEntries(
            priorityEntries: self::priorityFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: 'Routes',
                filename: $filename,
                priorityModules: $priorityModules,
            ),
            discoveredEntries: self::discoverNamedFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: 'Routes',
                filename: $filename,
            ),
        );
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function scanPolicyEntries(string $modulesRoot, string $basePath, array $priorityModules): array
    {
        return self::mergePrioritizedEntries(
            priorityEntries: self::priorityPolicyEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                priorityModules: $priorityModules,
            ),
            discoveredEntries: self::discoverPolicyFiles(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
            ),
        );
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function scanNamedDirectoryEntries(string $modulesRoot, string $basePath, string $directoryName, array $priorityModules): array
    {
        return self::mergePrioritizedEntries(
            priorityEntries: self::priorityDirectoryEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: $directoryName,
                priorityModules: $priorityModules,
            ),
            discoveredEntries: self::discoverNamedDirectoryEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: $directoryName,
            ),
        );
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function scanNamedFileEntries(string $modulesRoot, string $basePath, string $directoryName, string $filename, array $priorityModules): array
    {
        return self::mergePrioritizedEntries(
            priorityEntries: self::priorityFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: $directoryName,
                filename: $filename,
                priorityModules: $priorityModules,
            ),
            discoveredEntries: self::discoverNamedFileEntries(
                modulesRoot: $modulesRoot,
                basePath: $basePath,
                directoryName: $directoryName,
                filename: $filename,
            ),
        );
    }

    /**
     * @return list<string>
     */
    private static function discoverPolicyFiles(string $modulesRoot, string $basePath): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulesRoot, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        $entries = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if (! str_ends_with($file->getFilename(), 'Policy.php')) {
                continue;
            }

            $pathInfo = $file->getPathInfo();
            if (! $pathInfo instanceof SplFileInfo) {
                continue;
            }

            if ($pathInfo->getFilename() !== 'Policies') {
                continue;
            }

            $entries[] = self::relativePath($basePath, self::resolvedFilePath($file));
        }

        $entries = array_values(array_unique($entries));
        sort($entries, SORT_STRING);

        return $entries;
    }

    /**
     * @return list<string>
     */
    private static function discoverNamedFileEntries(string $modulesRoot, string $basePath, string $directoryName, string $filename): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulesRoot, RecursiveDirectoryIterator::SKIP_DOTS),
        );

        $entries = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if ($file->getFilename() !== $filename) {
                continue;
            }

            $pathInfo = $file->getPathInfo();
            if (! $pathInfo instanceof SplFileInfo) {
                continue;
            }

            if ($pathInfo->getFilename() !== $directoryName) {
                continue;
            }

            $entries[] = self::relativePath($basePath, self::resolvedFilePath($file));
        }

        $entries = array_values(array_unique($entries));
        sort($entries, SORT_STRING);

        return $entries;
    }

    /**
     * @return list<string>
     */
    private static function discoverNamedDirectoryEntries(string $modulesRoot, string $basePath, string $directoryName): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($modulesRoot, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        $entries = [];

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isDir()) {
                continue;
            }

            if ($file->getFilename() !== $directoryName) {
                continue;
            }

            $entries[] = self::relativePath($basePath, self::resolvedFilePath($file));
        }

        $entries = array_values(array_unique($entries));
        sort($entries, SORT_STRING);

        return $entries;
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function priorityFileEntries(string $modulesRoot, string $basePath, string $directoryName, string $filename, array $priorityModules): array
    {
        $entries = [];

        foreach ($priorityModules as $priorityModule) {
            $normalizedModulePath = mb_trim($priorityModule, " \t\n\r\0\x0B\\/");

            if ($normalizedModulePath === '') {
                continue;
            }

            $modulePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedModulePath);
            $path = $modulesRoot.DIRECTORY_SEPARATOR.$modulePath.DIRECTORY_SEPARATOR.$directoryName.DIRECTORY_SEPARATOR.$filename;

            if (is_file($path)) {
                $entries[] = self::relativePath($basePath, self::resolvedPath($path));
            }
        }

        return array_values(array_unique($entries));
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function priorityDirectoryEntries(string $modulesRoot, string $basePath, string $directoryName, array $priorityModules): array
    {
        $entries = [];

        foreach ($priorityModules as $priorityModule) {
            $normalizedModulePath = mb_trim($priorityModule, " \t\n\r\0\x0B\\/");

            if ($normalizedModulePath === '') {
                continue;
            }

            $modulePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedModulePath);
            $path = $modulesRoot.DIRECTORY_SEPARATOR.$modulePath.DIRECTORY_SEPARATOR.$directoryName;

            if (is_dir($path)) {
                $entries[] = self::relativePath($basePath, self::resolvedPath($path));
            }
        }

        return array_values(array_unique($entries));
    }

    /**
     * @param  list<string>  $priorityModules
     * @return list<string>
     */
    private static function priorityPolicyEntries(string $modulesRoot, string $basePath, array $priorityModules): array
    {
        $entries = [];

        foreach ($priorityModules as $priorityModule) {
            $normalizedModulePath = mb_trim($priorityModule, " \t\n\r\0\x0B\\/");

            if ($normalizedModulePath === '') {
                continue;
            }

            $modulePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $normalizedModulePath);
            $policiesPath = $modulesRoot.DIRECTORY_SEPARATOR.$modulePath.DIRECTORY_SEPARATOR.'Policies';

            if (! is_dir($policiesPath)) {
                continue;
            }

            $files = glob($policiesPath.DIRECTORY_SEPARATOR.'*Policy.php');
            $files = is_array($files) ? $files : [];
            sort($files, SORT_STRING);

            foreach ($files as $file) {
                if (is_file($file)) {
                    $entries[] = self::relativePath($basePath, self::resolvedPath($file));
                }
            }
        }

        return array_values(array_unique($entries));
    }

    /**
     * @param  list<string>  $priorityEntries
     * @param  list<string>  $discoveredEntries
     * @return list<string>
     */
    private static function mergePrioritizedEntries(array $priorityEntries, array $discoveredEntries): array
    {
        $priorityEntries = array_values(array_unique($priorityEntries));
        $priorityLookup = array_fill_keys($priorityEntries, true);

        $remainingEntries = array_values(array_filter(
            $discoveredEntries,
            static fn (string $entry): bool => ! isset($priorityLookup[$entry]),
        ));

        return [...$priorityEntries, ...$remainingEntries];
    }

    private static function relativePath(string $basePath, string $absolutePath): string
    {
        $normalizedBasePath = str_replace('\\', '/', self::normalizeBasePath($basePath));
        $normalizedAbsolutePath = str_replace('\\', '/', $absolutePath);

        if (str_starts_with($normalizedAbsolutePath, $normalizedBasePath.'/')) {
            return mb_substr($normalizedAbsolutePath, mb_strlen($normalizedBasePath) + 1);
        }

        return $normalizedAbsolutePath;
    }

    private static function resolvedFilePath(SplFileInfo $file): string
    {
        $realPath = $file->getRealPath();

        return is_string($realPath) ? $realPath : $file->getPathname();
    }

    private static function resolvedPath(string $path): string
    {
        $realPath = realpath($path);

        return is_string($realPath) ? $realPath : $path;
    }

    /**
     * @return class-string<ServiceProvider>|null
     */
    private static function providerClassFromPath(string $basePath, string $providerFile): ?string
    {
        $relativePath = str_replace('\\', '/', self::relativePath($basePath, $providerFile));

        if (! str_starts_with($relativePath, 'app/') || ! str_ends_with($relativePath, '.php')) {
            return null;
        }

        $classPath = mb_substr($relativePath, 4, -4);

        if ($classPath === '') {
            return null;
        }

        $providerClass = 'App\\'.str_replace('/', '\\', $classPath);

        if (! class_exists($providerClass) || ! is_subclass_of($providerClass, ServiceProvider::class)) {
            return null;
        }

        /** @var class-string<ServiceProvider> $providerClass */
        return $providerClass;
    }

    /**
     * @return class-string|null
     */
    private static function policyClassFromPath(string $basePath, string $policyFile): ?string
    {
        $relativePath = str_replace('\\', '/', self::relativePath($basePath, $policyFile));

        if (! str_starts_with($relativePath, 'app/') || ! str_ends_with($relativePath, '.php')) {
            return null;
        }

        $classPath = mb_substr($relativePath, 4, -4);

        if ($classPath === '') {
            return null;
        }

        $policyClass = 'App\\'.str_replace('/', '\\', $classPath);

        if (! class_exists($policyClass)) {
            return null;
        }

        return $policyClass;
    }

    /**
     * @param  class-string  $policyClass
     * @return class-string|null
     */
    private static function modelClassFromPolicyClass(string $policyClass): ?string
    {
        $policyBaseName = class_basename($policyClass);

        if (! str_ends_with($policyBaseName, 'Policy')) {
            return null;
        }

        $modelBaseName = mb_substr($policyBaseName, 0, -6);

        if ($modelBaseName === '') {
            return null;
        }

        $modelClass = 'App\\Models\\'.$modelBaseName;

        if (! class_exists($modelClass)) {
            return null;
        }

        return $modelClass;
    }

    /**
     * @param  list<string>  $entries
     * @return list<string>
     */
    private static function resolveEntries(string $basePath, array $entries): array
    {
        $normalizedBasePath = self::normalizeBasePath($basePath);

        return array_map(static function (string $entry) use ($normalizedBasePath): string {
            if (preg_match('/^(?:[A-Za-z]:[\/\\\\]|\/|\\\\)/', $entry) === 1) {
                return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $entry);
            }

            $normalizedEntry = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $entry);

            return $normalizedBasePath.DIRECTORY_SEPARATOR.$normalizedEntry;
        }, $entries);
    }

    /**
     * @param  array{
     *     version: int,
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     policies: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * } $payload
     * @return array{
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     policies: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    private static function resolvePayloadEntries(string $basePath, array $payload): array
    {
        return [
            'routes' => [
                'web' => self::resolveEntries($basePath, $payload['routes']['web']),
                'api' => self::resolveEntries($basePath, $payload['routes']['api']),
            ],
            'gates' => self::resolveEntries($basePath, $payload['gates']),
            'policies' => self::resolveEntries($basePath, $payload['policies']),
            'channels' => self::resolveEntries($basePath, $payload['channels']),
            'listeners' => self::resolveEntries($basePath, $payload['listeners']),
            'providers' => self::resolveEntries($basePath, $payload['providers']),
        ];
    }

    /**
     * @return array{
     *     version: int,
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     policies: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }|null
     */
    private static function loadCachedPayload(string $basePath): ?array
    {
        $cachePath = self::cachePath($basePath);

        if (! is_file($cachePath)) {
            return null;
        }

        $payload = require $cachePath;

        if (! is_array($payload)) {
            return null;
        }

        /** @var array<string, mixed> $payload */
        $normalizedPayload = self::normalizePayload($payload);

        if ($normalizedPayload['version'] !== self::CACHE_VERSION) {
            return null;
        }

        return $normalizedPayload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     version: int,
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     policies: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    private static function normalizePayload(array $payload): array
    {
        $routes = $payload['routes'] ?? [];

        if (! is_array($routes)) {
            $routes = [];
        }

        return [
            'version' => is_int($payload['version'] ?? null)
                ? $payload['version']
                : 0,
            'routes' => [
                'web' => self::normalizeEntryList($routes['web'] ?? []),
                'api' => self::normalizeEntryList($routes['api'] ?? []),
            ],
            'gates' => self::normalizeEntryList($payload['gates'] ?? []),
            'policies' => self::normalizeEntryList($payload['policies'] ?? []),
            'channels' => self::normalizeEntryList($payload['channels'] ?? []),
            'listeners' => self::normalizeEntryList($payload['listeners'] ?? []),
            'providers' => self::normalizeEntryList($payload['providers'] ?? []),
        ];
    }

    /**
     * @return list<string>
     */
    private static function normalizeEntryList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $entries = array_values(array_filter(
            array_map(
                static fn (mixed $entry): ?string => is_string($entry) && $entry !== ''
                    ? str_replace('\\', '/', $entry)
                    : null,
                $value,
            ),
            static fn (?string $entry): bool => $entry !== null,
        ));

        return array_values(array_unique($entries));
    }

    /**
     * @param  array{
     *     version: int,
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     policies: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * } $payload
     */
    private static function payloadHasMissingEntries(string $basePath, array $payload): bool
    {
        if (self::hasMissingFiles($basePath, $payload['routes']['web'])) {
            return true;
        }

        if (self::hasMissingFiles($basePath, $payload['routes']['api'])) {
            return true;
        }

        if (self::hasMissingFiles($basePath, $payload['gates'])) {
            return true;
        }

        if (self::hasMissingFiles($basePath, $payload['policies'])) {
            return true;
        }

        if (self::hasMissingFiles($basePath, $payload['channels'])) {
            return true;
        }

        if (self::hasMissingDirectories($basePath, $payload['listeners'])) {
            return true;
        }

        return self::hasMissingFiles($basePath, $payload['providers']);
    }

    /**
     * @param  list<string>  $entries
     */
    private static function hasMissingFiles(string $basePath, array $entries): bool
    {
        return array_any(self::resolveEntries($basePath, $entries), fn (string $entry): bool => ! is_file($entry));
    }

    /**
     * @param  list<string>  $entries
     */
    private static function hasMissingDirectories(string $basePath, array $entries): bool
    {
        return array_any(self::resolveEntries($basePath, $entries), fn (string $entry): bool => ! is_dir($entry));
    }

    /**
     * @return array{
     *     version: int,
     *     routes: array{web: list<string>, api: list<string>},
     *     gates: list<string>,
     *     policies: list<string>,
     *     channels: list<string>,
     *     listeners: list<string>,
     *     providers: list<string>
     * }
     */
    private static function emptyPayload(): array
    {
        return [
            'version' => self::CACHE_VERSION,
            'routes' => [
                'web' => [],
                'api' => [],
            ],
            'gates' => [],
            'policies' => [],
            'channels' => [],
            'listeners' => [],
            'providers' => [],
        ];
    }
}
