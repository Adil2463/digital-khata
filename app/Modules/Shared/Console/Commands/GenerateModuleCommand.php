<?php

declare(strict_types=1);

namespace App\Modules\Shared\Console\Commands;

use App\Enums\UserRole;
use App\Modules\Shared\Support\ModuleGeneration\ApiRouteProfile;
use App\Modules\Shared\Support\ModuleGeneration\CrudApiManifest;
use App\Modules\Shared\Support\ModuleGeneration\CrudResourceManifest;
use App\Modules\Shared\Support\ModuleGeneration\GenerateModuleInput;
use App\Modules\Shared\Support\ModuleGeneration\ModuleName;
use App\Modules\Shared\Support\ModuleGeneration\ModuleScaffoldExecutor;
use App\Modules\Shared\Support\ModuleGeneration\ModuleScaffoldPlan;
use App\Modules\Shared\Support\ModuleGeneration\ModuleScaffoldPlanner;
use App\Modules\Shared\Support\ModuleGeneration\PlannedFile;
use App\Modules\Shared\Support\ModuleGeneration\RouteProfile;
use App\Modules\Shared\Support\ModuleGeneration\ScaffoldType;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class GenerateModuleCommand extends Command
{
    protected $signature = 'generate:module
        {module : Module name (PascalCase, kebab-case, snake_case, or path-like)}
        {--page=Index : Initial page name}
        {--extend : Extend an existing module with page scaffolding}
        {--scaffold= : Scaffold target [page|crud|api|crud-api]}
        {--route-profile= : Route profile [app|public|custom]}
        {--route-prefix= : URI prefix for generated web routes}
        {--route-name-prefix= : Route name prefix for generated web routes}
        {--middleware= : Comma-separated middleware for generated web routes}
        {--roles= : Allowed user roles for app CRUD and protected API routes [all|comma-separated UserRole values]}
        {--api-route-profile= : API route profile [protected|public|custom]}
        {--api-route-prefix= : URI prefix for generated API routes}
        {--api-route-name-prefix= : Route name prefix for generated API routes}
        {--api-middleware= : Comma-separated middleware for generated API routes}
        {--no-api-resource : Skip API JsonResource generation}
        {--no-api-test : Skip API feature test generation}
        {--no-model : Skip model + migration generation}
        {--no-page : Skip frontend page/form/test generation}
        {--no-file-prompts : Disable interactive per-file generation prompts}
        {--force : Overwrite target files if they already exist}
        {--dry-run : Print planned operations without writing files}
        {--base-path= : Override base path (testing only)}';

    protected $description = 'Scaffold a backend + frontend module and optionally extend an existing module with new page contracts.';

    public function __construct(
        private readonly ModuleScaffoldPlanner $moduleScaffoldPlanner,
        private readonly ModuleScaffoldExecutor $moduleScaffoldExecutor,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $input = $this->resolveInput();
            $plan = $this->moduleScaffoldPlanner->plan($input);
            $plan = $this->applyPerFileSelection($input, $plan);

            if ($plan->files === []) {
                $this->warn('No files selected. Nothing to generate.');

                return self::SUCCESS;
            }

            $result = $this->moduleScaffoldExecutor->execute($plan, $input->force, $input->dryRun);

            if ($result->dryRun) {
                $this->warn('Dry run mode enabled. No files were written.');
            }

            foreach ($result->directories as $directory) {
                $label = $result->dryRun ? '[DRY-RUN] mkdir' : '[DIR]';
                $this->line(sprintf('%s %s', $label, $this->relativePath($input->basePath, $directory)));
            }

            foreach ($result->files as $file) {
                $overwritten = in_array($file, $result->overwrittenFiles, true);

                $label = $result->dryRun
                    ? '[DRY-RUN] write'
                    : ($overwritten ? '[OVERWRITE]' : '[FILE]');

                $this->line(sprintf('%s %s', $label, $this->relativePath($input->basePath, $file)));
            }

            $summaryPrefix = $result->dryRun ? 'Planned' : 'Generated';
            $this->info(sprintf(
                '%s %d directories and %d files for module "%s".',
                $summaryPrefix,
                count($result->directories),
                count($result->files),
                $input->moduleName->namespace,
            ));

            return self::SUCCESS;
        } catch (Throwable $throwable) {
            $this->error($throwable->getMessage());

            return self::FAILURE;
        }
    }

    private function resolveInput(): GenerateModuleInput
    {
        $moduleName = ModuleName::fromString($this->input()->string('module')->toString());
        $pageName = $this->input()->string('page')->toString();
        $basePathOption = $this->input()->string('base-path')->toString();
        $basePath = mb_trim(
            $basePathOption !== ''
                ? $basePathOption
                : base_path(),
        );
        $pagePascalName = $this->toPascalCase($pageName);
        $scaffoldType = $this->resolveScaffoldType();
        $generateCrud = ScaffoldType::includesCrud($scaffoldType);
        $generateApi = ScaffoldType::includesApi($scaffoldType);
        $hasWebScaffold = $generateCrud || $scaffoldType === ScaffoldType::PAGE;
        $crudResourceManifest = $generateCrud
            ? CrudResourceManifest::load(CrudResourceManifest::filePath($basePath, $moduleName, $pagePascalName))
            : null;

        $routeProfile = $generateCrud || ($hasWebScaffold && $this->option('route-profile') !== null)
            ? $this->resolveRouteProfile($crudResourceManifest?->routeProfile)
            : RouteProfile::APP;
        [$apiRouteProfile, $apiRoutePrefix, $apiRouteNamePrefix, $apiMiddleware] = $generateApi
            ? $this->resolveApiRouteConfiguration($moduleName, $crudResourceManifest)
            : $this->defaultApiRouteConfiguration($moduleName);
        $allowedRoles = $this->resolveAllowedRoles(
            requiresRoleScope: ($generateCrud && $routeProfile === RouteProfile::APP)
            || ($generateApi && $apiRouteProfile === ApiRouteProfile::PROTECTED),
            defaultRoles: $crudResourceManifest?->allowedRoles,
        );
        [$routePrefix, $routeNamePrefix, $middleware] = $hasWebScaffold
            ? $this->resolveRouteConfiguration($moduleName, $routeProfile, $allowedRoles, $crudResourceManifest)
            : $this->defaultWebRouteConfiguration($moduleName);

        if ($generateApi && $apiRouteProfile === ApiRouteProfile::PROTECTED) {
            $apiMiddleware = $this->buildProtectedApiMiddleware($moduleName, $apiMiddleware, $allowedRoles);
        }

        $generatePage = $this->resolveGeneratePage($generateCrud || $scaffoldType === ScaffoldType::PAGE);
        $generateModel = $this->resolveGenerateModel(
            hasBackendScaffold: $generateCrud || $generateApi,
            moduleName: $moduleName,
            basePath: $basePath,
        );
        $generateApiResource = $this->resolveGenerateApiResource($generateApi, $crudResourceManifest);
        $generateApiFeatureTest = $this->resolveGenerateApiFeatureTest($generateApi, $crudResourceManifest);

        $resolvedCrudResourceManifest = $generateCrud
            ? ($crudResourceManifest instanceof CrudResourceManifest
                ? new CrudResourceManifest(
                    pagePascalName: $crudResourceManifest->pagePascalName,
                    modelClass: $crudResourceManifest->modelClass,
                    routeProfile: $routeProfile,
                    routePrefix: $routePrefix,
                    routeNamePrefix: $routeNamePrefix,
                    allowedRoles: $allowedRoles,
                    middleware: $middleware,
                    api: $crudResourceManifest->api->enabled || $generateApi
                    ? new CrudApiManifest(
                        enabled: $generateApi,
                        routeProfile: $apiRouteProfile,
                        routePrefix: $apiRoutePrefix,
                        routeNamePrefix: $apiRouteNamePrefix,
                        middleware: $apiMiddleware,
                        generatesResource: $generateApiResource,
                        generatesFeatureTest: $generateApiFeatureTest,
                    )
                    : $crudResourceManifest->api,
                    tableColumns: $crudResourceManifest->tableColumns,
                    mobileFields: $crudResourceManifest->mobileFields,
                    formFields: $crudResourceManifest->formFields,
                    realtimeEnabled: $crudResourceManifest->realtimeEnabled,
                )
                : null)
            : null;

        return GenerateModuleInput::fromValues(
            moduleName: $moduleName,
            pageName: $pageName,
            scaffoldType: $scaffoldType,
            generateCrud: $generateCrud,
            generateApi: $generateApi,
            extend: $this->input()->boolean('extend'),
            generatePage: $generatePage,
            routeProfile: $routeProfile,
            routePrefix: $routePrefix,
            routeNamePrefix: $routeNamePrefix,
            allowedRoles: $allowedRoles,
            middleware: $middleware,
            apiRouteProfile: $apiRouteProfile,
            apiRoutePrefix: $apiRoutePrefix,
            apiRouteNamePrefix: $apiRouteNamePrefix,
            apiMiddleware: $apiMiddleware,
            generateModel: $generateModel,
            generateApiResource: $generateApiResource,
            generateApiFeatureTest: $generateApiFeatureTest,
            force: $this->input()->boolean('force'),
            dryRun: $this->input()->boolean('dry-run'),
            basePath: $basePath,
            crudResourceManifest: $resolvedCrudResourceManifest,
        );
    }

    private function resolveScaffoldType(): string
    {
        $providedScaffoldType = mb_strtolower(mb_trim($this->input()->string('scaffold')->toString()));

        if ($providedScaffoldType === '') {
            $defaultScaffoldType = $this->input()->boolean('extend')
                ? ScaffoldType::PAGE
                : ScaffoldType::CRUD;

            if (! $this->input->isInteractive()) {
                return $defaultScaffoldType;
            }

            /** @var string $selectedScaffoldType */
            $selectedScaffoldType = $this->choice(
                question: 'Select scaffolding target for the generated module',
                choices: ScaffoldType::values(),
                default: $defaultScaffoldType,
            );

            $providedScaffoldType = $selectedScaffoldType;
        }

        if (! ScaffoldType::isValid($providedScaffoldType)) {
            throw new RuntimeException(
                sprintf(
                    'Invalid scaffold type "%s". Allowed values are: %s.',
                    $providedScaffoldType,
                    implode(', ', ScaffoldType::values()),
                ),
            );
        }

        return $providedScaffoldType;
    }

    private function resolveRouteProfile(?string $defaultProfile = null): string
    {
        $providedProfile = mb_strtolower(mb_trim($this->input()->string('route-profile')->toString()));

        if ($providedProfile === '') {
            if (! $this->input->isInteractive()) {
                return $defaultProfile ?? RouteProfile::APP;
            }

            /** @var string $selectedProfile */
            $selectedProfile = $this->choice(
                question: 'Select a route profile for the generated module',
                choices: RouteProfile::values(),
                default: $defaultProfile ?? RouteProfile::APP,
            );

            $providedProfile = $selectedProfile;
        }

        if (! RouteProfile::isValid($providedProfile)) {
            throw new RuntimeException(
                sprintf(
                    'Invalid route profile "%s". Allowed values are: %s.',
                    $providedProfile,
                    implode(', ', RouteProfile::values()),
                ),
            );
        }

        return $providedProfile;
    }

    /**
     * @return array{0: string, 1: string, 2: list<string>}
     */
    private function defaultWebRouteConfiguration(ModuleName $moduleName): array
    {
        return [
            'app/'.$moduleName->frontendKebab,
            'app.'.$moduleName->frontendKebab,
            ['auth', 'verified'],
        ];
    }

    /**
     * @param  list<string>  $allowedRoles
     * @return array{0: string, 1: string, 2: list<string>}
     */
    private function resolveRouteConfiguration(ModuleName $moduleName, string $routeProfile, array $allowedRoles, ?CrudResourceManifest $crudResourceManifest = null): array
    {
        [$defaultPrefix, $defaultNamePrefix, $defaultMiddleware] = $crudResourceManifest instanceof CrudResourceManifest
            ? [
                $crudResourceManifest->routePrefix,
                $crudResourceManifest->routeNamePrefix,
                $crudResourceManifest->middleware,
            ]
            : match ($routeProfile) {
                RouteProfile::APP => $this->isAdminOnlyRoleScope($allowedRoles)
                ? [
                    'app/admin/'.$moduleName->frontendKebab,
                    'app.admin.'.$moduleName->frontendKebab,
                    $this->buildAppRoleAwareMiddleware($moduleName, $allowedRoles),
                ]
                : [
                    'app/'.$moduleName->frontendKebab,
                    'app.'.$moduleName->frontendKebab,
                    $this->buildAppRoleAwareMiddleware($moduleName, $allowedRoles),
                ],
                RouteProfile::PUBLIC => [
                    $moduleName->frontendKebab,
                    $moduleName->frontendKebab,
                    [],
                ],
                RouteProfile::CUSTOM => [
                    'app/'.$moduleName->frontendKebab,
                    $moduleName->frontendKebab,
                    ['auth', 'verified'],
                ],
                default => throw new RuntimeException(sprintf('Unsupported route profile "%s".', $routeProfile)),
            };

        $routePrefix = mb_trim($this->input()->string('route-prefix')->toString());
        $routeNamePrefix = mb_trim($this->input()->string('route-name-prefix')->toString());
        $middlewareOption = mb_trim($this->input()->string('middleware')->toString());

        if ($routeProfile === RouteProfile::CUSTOM && $this->input->isInteractive()) {
            if ($routePrefix === '') {
                $routePrefix = $this->askString('Enter route URI prefix', $defaultPrefix);
            }

            if ($routeNamePrefix === '') {
                $routeNamePrefix = $this->askString('Enter route name prefix', $defaultNamePrefix);
            }

            if ($middlewareOption === '') {
                $middlewareOption = $this->askString(
                    'Enter middleware list (comma-separated, leave blank for none)',
                    implode(',', $defaultMiddleware),
                );
            }
        }

        $resolvedPrefix = $routePrefix !== '' ? $routePrefix : $defaultPrefix;
        $resolvedRouteNamePrefix = $routeNamePrefix !== '' ? $routeNamePrefix : $defaultNamePrefix;
        $resolvedMiddleware = $middlewareOption !== ''
            ? $this->parseMiddleware($middlewareOption)
            : $defaultMiddleware;

        if ($routeProfile === RouteProfile::APP) {
            $resolvedMiddleware = $this->appendRoleMiddlewareIfNeeded($moduleName, $resolvedMiddleware, $allowedRoles);
        }

        return [$resolvedPrefix, $resolvedRouteNamePrefix, $resolvedMiddleware];
    }

    /**
     * @param  list<string>|null  $defaultRoles
     * @return list<string>
     */
    private function resolveAllowedRoles(bool $requiresRoleScope, ?array $defaultRoles = null): array
    {
        if (! $requiresRoleScope) {
            return [];
        }

        $roleOption = mb_strtolower(mb_trim($this->input()->string('roles')->toString()));

        if ($roleOption === '') {
            if (! $this->input->isInteractive()) {
                if ($defaultRoles !== null) {
                    return $defaultRoles;
                }

                throw new RuntimeException(sprintf(
                    'The --roles option is required for app CRUD or protected API scaffolding. Use --roles=all or comma-separated values: %s.',
                    implode(', ', $this->availableUserRoleValues()),
                ));
            }

            $roleOption = mb_strtolower($this->askString(
                sprintf(
                    'Select allowed roles for protected generated routes (%s)',
                    implode(', ', array_merge(['all'], $this->availableUserRoleValues())),
                ),
                $defaultRoles === null || $defaultRoles === []
                ? 'all'
                : implode(',', $defaultRoles),
            ));
        }

        return $this->parseAllowedRoles($roleOption);
    }

    /**
     * @return list<string>
     */
    private function parseAllowedRoles(string $value): array
    {
        $rawRoles = $this->parseMiddleware($value);

        if ($rawRoles === []) {
            throw new RuntimeException('At least one role is required. Use --roles=all or comma-separated UserRole values.');
        }

        $normalizedRoles = array_map(
            mb_strtolower(...),
            $rawRoles,
        );
        $uniqueRoles = array_values(array_unique($normalizedRoles));

        if (in_array('all', $uniqueRoles, true)) {
            if (count($uniqueRoles) > 1) {
                throw new RuntimeException('The "all" role scope cannot be combined with explicit roles.');
            }

            return [];
        }

        $availableRoleValues = $this->availableUserRoleValues();

        foreach ($uniqueRoles as $uniqueRole) {
            if (! in_array($uniqueRole, $availableRoleValues, true)) {
                throw new RuntimeException(sprintf(
                    'Invalid role "%s". Allowed roles: all, %s.',
                    $uniqueRole,
                    implode(', ', $availableRoleValues),
                ));
            }
        }

        $roleOrder = array_flip($availableRoleValues);
        usort($uniqueRoles, static fn (string $left, string $right): int => ($roleOrder[$left] ?? 999) <=> ($roleOrder[$right] ?? 999));

        return $uniqueRoles;
    }

    /**
     * @return list<string>
     */
    private function availableUserRoleValues(): array
    {
        return array_map(
            static fn (UserRole $userRole): string => $userRole->value,
            UserRole::cases(),
        );
    }

    /**
     * @param  list<string>  $allowedRoles
     * @return list<string>
     */
    private function buildAppRoleAwareMiddleware(ModuleName $moduleName, array $allowedRoles): array
    {
        return $this->appendRoleMiddlewareIfNeeded(
            moduleName: $moduleName,
            middleware: ['auth', 'verified'],
            allowedRoles: $allowedRoles,
        );
    }

    /**
     * @param  list<string>  $middleware
     * @param  list<string>  $allowedRoles
     * @return list<string>
     */
    private function buildProtectedApiMiddleware(ModuleName $moduleName, array $middleware, array $allowedRoles): array
    {
        if (! in_array('auth:sanctum', $middleware, true)) {
            $middleware[] = 'auth:sanctum';
        }

        return $this->appendRoleMiddlewareIfNeeded($moduleName, $middleware, $allowedRoles);
    }

    /**
     * @param  list<string>  $middleware
     * @param  list<string>  $allowedRoles
     * @return list<string>
     */
    private function appendRoleMiddlewareIfNeeded(ModuleName $moduleName, array $middleware, array $allowedRoles): array
    {
        if ($allowedRoles === []) {
            return $middleware;
        }

        $abilityMiddleware = 'can:'.$this->moduleAbilityName($moduleName);

        if (! in_array($abilityMiddleware, $middleware, true)) {
            $middleware[] = $abilityMiddleware;
        }

        return $middleware;
    }

    /**
     * @param  list<string>  $allowedRoles
     */
    private function isAdminOnlyRoleScope(array $allowedRoles): bool
    {
        return count($allowedRoles) === 1
            && in_array(UserRole::Admin->value, $allowedRoles, true);
    }

    private function moduleAbilityName(ModuleName $moduleName): string
    {
        return 'manage-'.$moduleName->frontendKebab;
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: list<string>}
     */
    private function resolveApiRouteConfiguration(ModuleName $moduleName, ?CrudResourceManifest $crudResourceManifest = null): array
    {
        $providedApiProfile = mb_strtolower(mb_trim($this->input()->string('api-route-profile')->toString()));
        $manifestApi = $crudResourceManifest?->api;

        if ($providedApiProfile === '') {
            if (! $this->input->isInteractive()) {
                $providedApiProfile = $manifestApi instanceof CrudApiManifest
                    ? $manifestApi->routeProfile
                    : ApiRouteProfile::PROTECTED;
            } else {
                /** @var string $selectedApiProfile */
                $selectedApiProfile = $this->choice(
                    question: 'Select an API route profile for the generated module',
                    choices: ApiRouteProfile::values(),
                    default: $manifestApi instanceof CrudApiManifest
                    ? $manifestApi->routeProfile
                    : ApiRouteProfile::PROTECTED,
                );

                $providedApiProfile = $selectedApiProfile;
            }
        }

        if (! ApiRouteProfile::isValid($providedApiProfile)) {
            throw new RuntimeException(
                sprintf(
                    'Invalid API route profile "%s". Allowed values are: %s.',
                    $providedApiProfile,
                    implode(', ', ApiRouteProfile::values()),
                ),
            );
        }

        [$computedPrefix, $computedNamePrefix, $computedMiddleware] = $this->defaultApiRouteConfigurationForProfile(
            moduleName: $moduleName,
            apiRouteProfile: $providedApiProfile,
        );

        $defaultPrefix = $manifestApi instanceof CrudApiManifest ? $manifestApi->routePrefix : $computedPrefix;
        $defaultNamePrefix = $manifestApi instanceof CrudApiManifest ? $manifestApi->routeNamePrefix : $computedNamePrefix;
        $defaultMiddleware = $manifestApi instanceof CrudApiManifest ? $manifestApi->middleware : $computedMiddleware;

        $apiRoutePrefix = mb_trim($this->input()->string('api-route-prefix')->toString());
        $apiRouteNamePrefix = mb_trim($this->input()->string('api-route-name-prefix')->toString());
        $apiMiddlewareOption = mb_trim($this->input()->string('api-middleware')->toString());

        if ($providedApiProfile === ApiRouteProfile::CUSTOM && $this->input->isInteractive()) {
            if ($apiRoutePrefix === '') {
                $apiRoutePrefix = $this->askString('Enter API route URI prefix', $defaultPrefix);
            }

            if ($apiRouteNamePrefix === '') {
                $apiRouteNamePrefix = $this->askString('Enter API route name prefix', $defaultNamePrefix);
            }

            if ($apiMiddlewareOption === '') {
                $apiMiddlewareOption = $this->askString(
                    'Enter API middleware list (comma-separated, leave blank for none)',
                    implode(',', $defaultMiddleware),
                );
            }
        }

        $resolvedApiRoutePrefix = $apiRoutePrefix !== '' ? $apiRoutePrefix : $defaultPrefix;
        $resolvedApiRouteNamePrefix = $apiRouteNamePrefix !== '' ? $apiRouteNamePrefix : $defaultNamePrefix;
        $resolvedApiMiddleware = $apiMiddlewareOption !== ''
            ? $this->parseMiddleware($apiMiddlewareOption)
            : $defaultMiddleware;

        return [$providedApiProfile, $resolvedApiRoutePrefix, $resolvedApiRouteNamePrefix, $resolvedApiMiddleware];
    }

    /**
     * @return array{0: string, 1: string, 2: string, 3: list<string>}
     */
    private function defaultApiRouteConfiguration(ModuleName $moduleName): array
    {
        [$prefix, $namePrefix, $middleware] = $this->defaultApiRouteConfigurationForProfile(
            moduleName: $moduleName,
            apiRouteProfile: ApiRouteProfile::PROTECTED,
        );

        return [ApiRouteProfile::PROTECTED, $prefix, $namePrefix, $middleware];
    }

    /**
     * @return array{0: string, 1: string, 2: list<string>}
     */
    private function defaultApiRouteConfigurationForProfile(ModuleName $moduleName, string $apiRouteProfile): array
    {
        return match ($apiRouteProfile) {
            ApiRouteProfile::PROTECTED => [
                'v1/admin/'.$moduleName->frontendKebab,
                'api.v1.admin.'.$moduleName->frontendKebab,
                ['auth:sanctum'],
            ],
            ApiRouteProfile::PUBLIC => [
                'v1/'.$moduleName->frontendKebab,
                'api.v1.'.$moduleName->frontendKebab,
                [],
            ],
            ApiRouteProfile::CUSTOM => [
                'v1/admin/'.$moduleName->frontendKebab,
                'api.v1.admin.'.$moduleName->frontendKebab,
                ['auth:sanctum'],
            ],
            default => throw new RuntimeException(sprintf('Unsupported API route profile "%s".', $apiRouteProfile)),
        };
    }

    /**
     * @return list<string>
     */
    private function parseMiddleware(string $value): array
    {
        $items = explode(',', $value);

        $middleware = [];

        foreach ($items as $item) {
            $trimmedItem = mb_trim($item);

            if ($trimmedItem !== '') {
                $middleware[] = $trimmedItem;
            }
        }

        return $middleware;
    }

    private function resolveGeneratePage(bool $generateCrud): bool
    {
        return $generateCrud && ! $this->input()->boolean('no-page');
    }

    private function resolveGenerateModel(bool $hasBackendScaffold, ModuleName $moduleName, string $basePath): bool
    {
        if (! $hasBackendScaffold || $this->input()->boolean('no-model')) {
            return false;
        }

        if (! $this->input()->boolean('extend')) {
            return true;
        }

        return $this->isModelOrMigrationMissing($moduleName, $basePath);
    }

    private function isModelOrMigrationMissing(ModuleName $moduleName, string $basePath): bool
    {
        $modelClass = implode('', $moduleName->namespaceSegments);
        $modelPath = sprintf('%s/app/Models/%s.php', $basePath, $modelClass);

        if (! is_file($modelPath)) {
            return true;
        }

        $tableName = Str::snake(Str::pluralStudly($modelClass));
        $migrationFiles = glob($basePath.sprintf('/database/migrations/*_create_%s_table.php', $tableName));
        $migrationFiles = is_array($migrationFiles) ? $migrationFiles : [];

        return $migrationFiles === [];
    }

    private function resolveGenerateApiResource(bool $generateApi, ?CrudResourceManifest $crudResourceManifest = null): bool
    {
        return $generateApi
            && ! $this->input()->boolean('no-api-resource')
            && ($crudResourceManifest?->api->generatesResource ?? true);
    }

    private function resolveGenerateApiFeatureTest(bool $generateApi, ?CrudResourceManifest $crudResourceManifest = null): bool
    {
        return $generateApi
            && ! $this->input()->boolean('no-api-test')
            && ($crudResourceManifest?->api->generatesFeatureTest ?? true);
    }

    private function relativePath(string $basePath, string $absolutePath): string
    {
        $normalizedBasePath = str_replace('\\', '/', mb_rtrim($basePath, '\\/'));
        $normalizedAbsolutePath = str_replace('\\', '/', $absolutePath);

        if (str_starts_with($normalizedAbsolutePath, $normalizedBasePath.'/')) {
            return mb_substr($normalizedAbsolutePath, mb_strlen($normalizedBasePath) + 1);
        }

        return $normalizedAbsolutePath;
    }

    private function askString(string $question, string $default = ''): string
    {
        $response = $this->ask($question, $default);

        if (! is_string($response)) {
            return mb_trim($default);
        }

        return mb_trim($response);
    }

    private function toPascalCase(string $value): string
    {
        $spaced = preg_replace('/([a-z0-9])([A-Z])/u', '$1 $2', $value);

        if (! is_string($spaced)) {
            throw new RuntimeException('Could not derive a valid page name from the provided value.');
        }

        $parts = preg_split('/[^a-zA-Z0-9]+/u', $spaced);
        $parts = is_array($parts) ? $parts : [];

        $normalizedParts = [];

        foreach ($parts as $part) {
            $trimmedPart = mb_trim($part);

            if ($trimmedPart !== '') {
                $normalizedParts[] = ucfirst(mb_strtolower($trimmedPart));
            }
        }

        if ($normalizedParts === []) {
            throw new RuntimeException('Could not derive a valid page name from the provided value.');
        }

        return implode('', $normalizedParts);
    }

    private function applyPerFileSelection(GenerateModuleInput $generateModuleInput, ModuleScaffoldPlan $moduleScaffoldPlan): ModuleScaffoldPlan
    {
        if (! $this->shouldPromptPerFile()) {
            return $moduleScaffoldPlan;
        }

        $selectedFiles = [];

        foreach ($moduleScaffoldPlan->files as $plannedFile) {
            $relativeFilePath = $this->relativePath($generateModuleInput->basePath, $plannedFile->path);
            $shouldGenerateFile = $this->confirm(sprintf('Generate file [%s]?', $relativeFilePath), true);

            if ($shouldGenerateFile) {
                $selectedFiles[] = $plannedFile;
            }
        }

        $selectedDirectories = $this->resolveDirectoriesForFiles($moduleScaffoldPlan->directories, $selectedFiles);

        return new ModuleScaffoldPlan(
            moduleExists: $moduleScaffoldPlan->moduleExists,
            directories: $selectedDirectories,
            files: $selectedFiles,
        );
    }

    private function shouldPromptPerFile(): bool
    {
        return $this->input->isInteractive() && ! $this->input()->boolean('no-file-prompts');
    }

    /**
     * @param  list<string>  $directories
     * @param  list<PlannedFile>  $files
     * @return list<string>
     */
    private function resolveDirectoriesForFiles(array $directories, array $files): array
    {
        if ($files === []) {
            return [];
        }

        $directoriesToGenerate = array_values(array_filter($directories, function (string $directory) use ($files): bool {
            $normalizedDirectory = str_replace('\\', '/', mb_rtrim($directory, '\\/'));

            foreach ($files as $file) {
                $normalizedFilePath = str_replace('\\', '/', $file->path);

                if (str_starts_with($normalizedFilePath, $normalizedDirectory.'/')) {
                    return true;
                }
            }

            return false;
        }));

        sort($directoriesToGenerate, SORT_STRING);

        return $directoriesToGenerate;
    }
}
