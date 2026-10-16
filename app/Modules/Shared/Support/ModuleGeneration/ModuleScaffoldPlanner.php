<?php

declare(strict_types=1);

namespace App\Modules\Shared\Support\ModuleGeneration;

use App\Enums\UserRole;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;

final readonly class ModuleScaffoldPlanner
{
    public function __construct(
        private Filesystem $filesystem,
        private TemplateRenderer $templateRenderer,
    ) {}

    public function plan(GenerateModuleInput $generateModuleInput): ModuleScaffoldPlan
    {
        $moduleRootPath = $this->moduleRootPath($generateModuleInput);
        $moduleExists = $this->filesystem->isDirectory($moduleRootPath);

        if ($moduleExists && ! $generateModuleInput->extend) {
            throw new RuntimeException(
                sprintf(
                    'Module already exists at "%s". Use --extend to add page scaffolding to an existing module.',
                    $this->relativePath($generateModuleInput->basePath, $moduleRootPath),
                ),
            );
        }

        if (! $moduleExists && $generateModuleInput->extend) {
            throw new RuntimeException(
                sprintf(
                    'Module "%s" does not exist. Run the command without --extend to scaffold a new module first.',
                    $generateModuleInput->moduleName->namespace,
                ),
            );
        }

        $directories = [];
        $files = [];

        if ($generateModuleInput->generateCrud || $generateModuleInput->generateApi) {
            $this->appendSharedBackendSupportScaffold($generateModuleInput, $directories, $files);
        }

        if ($generateModuleInput->generateCrud) {
            $this->appendCrudBackendScaffold($generateModuleInput, $directories, $files);
            $this->appendFeatureTestScaffold($generateModuleInput, $directories, $files);
        }

        if ($this->shouldGenerateRoleGateFile($generateModuleInput)) {
            $this->appendCrudRoleGateScaffold($generateModuleInput, $directories, $files);
        }

        if ($generateModuleInput->generateApi) {
            $this->appendApiBackendScaffold($generateModuleInput, $directories, $files);

            if ($generateModuleInput->generateApiFeatureTest) {
                $this->appendApiFeatureTestScaffold($generateModuleInput, $directories, $files);
            }
        }

        if ($generateModuleInput->generateModel) {
            $this->appendModelAndMigrationScaffold($generateModuleInput, $directories, $files);
        }

        if ($generateModuleInput->generatePage) {
            $this->appendFrontendPageScaffold($generateModuleInput, $directories, $files);
        }

        $directories = array_values(array_unique($directories));
        sort($directories, SORT_STRING);
        $files = $this->uniquePlannedFiles($files);

        usort(
            $files,
            static fn (PlannedFile $left, PlannedFile $right): int => $left->path <=> $right->path,
        );

        return new ModuleScaffoldPlan(
            moduleExists: $moduleExists,
            directories: $directories,
            files: $files,
        );
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendSharedBackendSupportScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $moduleRootPath = $this->moduleRootPath($generateModuleInput);
        $requestsPath = $moduleRootPath.'/Http/Requests';
        $dataPath = $moduleRootPath.'/Data';
        $queriesPath = $moduleRootPath.'/Queries';
        $commandsPath = $moduleRootPath.'/Commands';
        $handlersPath = $moduleRootPath.'/Handlers';

        $directories[] = $requestsPath;
        $directories[] = $dataPath;
        $directories[] = $queriesPath;
        $directories[] = $commandsPath;

        if ($this->shouldGenerateHandlers($generateModuleInput)) {
            $directories[] = $handlersPath;
        }

        $tokens = [
            'moduleNamespace' => $generateModuleInput->moduleName->namespace,
            'pagePascalName' => $generateModuleInput->pagePascalName,
            'modelClass' => $this->modelClassName($generateModuleInput->moduleName),
            'modelVariable' => $this->modelVariableName($generateModuleInput->moduleName),
            'storeDataClass' => $this->storeDataClassName($generateModuleInput),
            'storeDataVariable' => lcfirst($this->storeDataClassName($generateModuleInput)),
        ];

        $files[] = new PlannedFile(
            path: sprintf('%s/%sStoreRequest.php', $requestsPath, $generateModuleInput->pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/request.stub'),
                $tokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%sUpdateRequest.php', $requestsPath, $generateModuleInput->pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/update-request.stub'),
                $tokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%s.php', $dataPath, $this->storeDataClassName($generateModuleInput)),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/data.stub'),
                $tokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%sQueries.php', $queriesPath, $this->modelClassName($generateModuleInput->moduleName)),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/query.stub'),
                $tokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%sCommands.php', $commandsPath, $this->modelClassName($generateModuleInput->moduleName)),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/command.stub'),
                $tokens,
            ),
        );

        if ($this->shouldGenerateHandlers($generateModuleInput)) {
            $files[] = new PlannedFile(
                path: sprintf('%s/%sQueryHandler.php', $handlersPath, $this->modelClassName($generateModuleInput->moduleName)),
                contents: $this->templateRenderer->render(
                    base_path('stubs/module-generation/backend/query-handler.stub'),
                    $tokens,
                ),
            );

            $files[] = new PlannedFile(
                path: sprintf('%s/%sCommandHandler.php', $handlersPath, $this->modelClassName($generateModuleInput->moduleName)),
                contents: $this->templateRenderer->render(
                    base_path('stubs/module-generation/backend/command-handler.stub'),
                    $tokens,
                ),
            );
        }
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendCrudBackendScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $moduleRootPath = $this->moduleRootPath($generateModuleInput);
        $controllersPath = $moduleRootPath.'/Http/Controllers';
        $routesPath = $moduleRootPath.'/Routes';
        $dataPath = $moduleRootPath.'/Data';
        $manifestsPath = $moduleRootPath.'/Manifests';
        $crudResourceManifest = $this->crudResourceManifest($generateModuleInput);

        $directories[] = $controllersPath;
        $directories[] = $routesPath;
        $directories[] = $dataPath;
        $directories[] = $manifestsPath;

        $moduleNamespace = $generateModuleInput->moduleName->namespace;
        $moduleKebab = $generateModuleInput->moduleName->frontendKebab;
        $pagePascalName = $generateModuleInput->pagePascalName;
        $pageCamelName = lcfirst($pagePascalName);
        $routeNamePrefix = $generateModuleInput->routeNamePrefix;
        $modelClass = $this->modelClassName($generateModuleInput->moduleName);
        $modelVariable = $this->modelVariableName($generateModuleInput->moduleName);
        $pageDataClass = $this->pageDataClassName($generateModuleInput);
        $listItemDataClass = $this->listItemDataClassName($generateModuleInput);

        $controllerTokens = [
            'moduleNamespace' => $moduleNamespace,
            'pagePascalName' => $pagePascalName,
            'pageCamelName' => $pageCamelName,
            'moduleKebab' => $moduleKebab,
            'routeStoreName' => $routeNamePrefix.'.store',
            'routeIndexName' => $routeNamePrefix.'.index',
            'pageTitle' => $pagePascalName,
            'modelClass' => $modelClass,
            'modelVariable' => $modelVariable,
            'pageDataClass' => $pageDataClass,
        ];

        $routeTokens = [
            'moduleNamespace' => $moduleNamespace,
            'pagePascalName' => $pagePascalName,
            'routePrefix' => $generateModuleInput->routePrefix,
            'routeNamePrefix' => $routeNamePrefix,
            'middlewareLine' => $this->buildMiddlewareLine($generateModuleInput->middleware),
            'modelRouteParameter' => '{'.$modelVariable.'}',
        ];

        $controllerStubPath = $this->shouldGenerateHandlers($generateModuleInput)
            ? base_path('stubs/module-generation/backend/controller-with-handlers.stub')
            : base_path('stubs/module-generation/backend/controller.stub');

        $pageDataTokens = [
            'moduleNamespace' => $moduleNamespace,
            'pagePascalName' => $pagePascalName,
            'modelClass' => $modelClass,
            'modelVariable' => $modelVariable,
            'pageDataClass' => $pageDataClass,
            'listItemDataClass' => $listItemDataClass,
        ];

        $files[] = new PlannedFile(
            path: sprintf('%s/%sController.php', $controllersPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                $controllerStubPath,
                $controllerTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%s.php', $dataPath, $listItemDataClass),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/list-item-data.stub'),
                $pageDataTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%s.php', $dataPath, $pageDataClass),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/page-data.stub'),
                $pageDataTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: CrudResourceManifest::filePath($generateModuleInput->basePath, $generateModuleInput->moduleName, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/resource-manifest.stub'),
                [
                    'pagePascalName' => $crudResourceManifest->pagePascalName,
                    'modelClass' => $crudResourceManifest->modelClass,
                    'routeProfile' => $crudResourceManifest->routeProfile,
                    'routePrefix' => $crudResourceManifest->routePrefix,
                    'routeNamePrefix' => $crudResourceManifest->routeNamePrefix,
                    'routeRoles' => $this->renderPhpStringList($crudResourceManifest->allowedRoles),
                    'routeMiddleware' => $this->renderPhpStringList($crudResourceManifest->middleware),
                    'apiEnabled' => $this->renderPhpBoolean($crudResourceManifest->api->enabled),
                    'apiRouteProfile' => $crudResourceManifest->api->routeProfile,
                    'apiRoutePrefix' => $crudResourceManifest->api->routePrefix,
                    'apiRouteNamePrefix' => $crudResourceManifest->api->routeNamePrefix,
                    'apiMiddleware' => $this->renderPhpStringList($crudResourceManifest->api->middleware),
                    'apiGeneratesResource' => $this->renderPhpBoolean($crudResourceManifest->api->generatesResource),
                    'apiGeneratesFeatureTest' => $this->renderPhpBoolean($crudResourceManifest->api->generatesFeatureTest),
                    'tableColumns' => $this->renderManifestTableColumns($crudResourceManifest),
                    'mobileFields' => $this->renderManifestMobileFields($crudResourceManifest),
                    'formFields' => $this->renderManifestFormFields($crudResourceManifest),
                    'realtimeEnabled' => $this->renderPhpBoolean($crudResourceManifest->realtimeEnabled),
                ],
            ),
        );

        $files[] = new PlannedFile(
            path: $routesPath.'/web.php',
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/web-routes.stub'),
                $routeTokens,
            ),
        );
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendCrudRoleGateScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $moduleRootPath = $this->moduleRootPath($generateModuleInput);
        $routesPath = $moduleRootPath.'/Routes';

        $directories[] = $routesPath;

        $abilityName = 'manage-'.$generateModuleInput->moduleName->frontendKebab;
        $allowedRoleCases = $this->allowedPhpRoleCases($generateModuleInput->allowedRoles);

        $tokens = [
            'abilityName' => $abilityName,
            // A single allowed role reads as a comparison rather than a one-element
            // `in_array`, which is also what Rector rewrites it to.
            'allowedRoleExpression' => count($allowedRoleCases) === 1
                ? '$user->role === '.$allowedRoleCases[0]
                : sprintf('in_array($user->role, [%s], true)', implode(', ', $allowedRoleCases)),
        ];

        $files[] = new PlannedFile(
            path: $routesPath.'/gates.php',
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/gates.stub'),
                $tokens,
            ),
        );
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendApiBackendScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $moduleRootPath = $this->moduleRootPath($generateModuleInput);
        $controllersPath = $moduleRootPath.'/Http/Controllers';
        $routesPath = $moduleRootPath.'/Routes';
        $resourcesPath = $moduleRootPath.'/Http/Resources';

        $directories[] = $controllersPath;
        $directories[] = $routesPath;

        if ($generateModuleInput->generateApiResource) {
            $directories[] = $resourcesPath;
        }

        $moduleNamespace = $generateModuleInput->moduleName->namespace;
        $pagePascalName = $generateModuleInput->pagePascalName;
        $pageCamelName = lcfirst($pagePascalName);
        $modelClass = $this->modelClassName($generateModuleInput->moduleName);
        $modelVariable = $this->modelVariableName($generateModuleInput->moduleName);

        $controllerTokens = [
            'moduleNamespace' => $moduleNamespace,
            'pagePascalName' => $pagePascalName,
            'pageCamelName' => $pageCamelName,
            'modelClass' => $modelClass,
            'modelVariable' => $modelVariable,
        ];

        $routeTokens = [
            'moduleNamespace' => $moduleNamespace,
            'pagePascalName' => $pagePascalName,
            'apiRoutePrefix' => $generateModuleInput->apiRoutePrefix,
            'apiRouteNamePrefix' => $generateModuleInput->apiRouteNamePrefix,
            'apiMiddlewareLine' => $this->buildMiddlewareLine($generateModuleInput->apiMiddleware),
            'modelRouteParameter' => '{'.$modelVariable.'}',
        ];

        $resourceTokens = [
            'moduleNamespace' => $moduleNamespace,
            'pagePascalName' => $pagePascalName,
            'modelClass' => $modelClass,
        ];

        $apiControllerStubPath = match (true) {
            $this->shouldGenerateHandlers($generateModuleInput) && $generateModuleInput->generateApiResource => base_path('stubs/module-generation/backend/api-controller-with-handlers.stub'),
            $this->shouldGenerateHandlers($generateModuleInput) && ! $generateModuleInput->generateApiResource => base_path('stubs/module-generation/backend/api-controller-no-resource-with-handlers.stub'),
            $generateModuleInput->generateApiResource => base_path('stubs/module-generation/backend/api-controller.stub'),
            default => base_path('stubs/module-generation/backend/api-controller-no-resource.stub'),
        };

        $files[] = new PlannedFile(
            path: sprintf('%s/%sApiController.php', $controllersPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                $apiControllerStubPath,
                $controllerTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: $routesPath.'/api.php',
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/backend/api-routes.stub'),
                $routeTokens,
            ),
        );

        if ($generateModuleInput->generateApiResource) {
            $files[] = new PlannedFile(
                path: sprintf('%s/%sResource.php', $resourcesPath, $pagePascalName),
                contents: $this->templateRenderer->render(
                    base_path('stubs/module-generation/backend/resource.stub'),
                    $resourceTokens,
                ),
            );
        }
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendModelAndMigrationScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $modelsPath = $generateModuleInput->basePath.'/app/Models';
        $migrationsPath = $generateModuleInput->basePath.'/database/migrations';

        $directories[] = $modelsPath;
        $directories[] = $migrationsPath;

        $moduleNamespace = $generateModuleInput->moduleName->namespace;
        $pagePascalName = $generateModuleInput->pagePascalName;
        $modelClass = $this->modelClassName($generateModuleInput->moduleName);
        $tableName = Str::snake(Str::pluralStudly($modelClass));

        $modelTokens = [
            'moduleNamespace' => $moduleNamespace,
            'pagePascalName' => $pagePascalName,
            'modelClass' => $modelClass,
            'modelVariable' => $this->modelVariableName($generateModuleInput->moduleName),
            'storeDataClass' => $this->storeDataClassName($generateModuleInput),
            'storeDataVariable' => lcfirst($this->storeDataClassName($generateModuleInput)),
            'tableName' => $tableName,
        ];

        $migrationTokens = [
            'tableName' => $tableName,
        ];

        $migrationFileName = sprintf(
            '%s_create_%s_table.php',
            date('Y_m_d_His'),
            $tableName,
        );

        $modelFilePath = sprintf('%s/%s.php', $modelsPath, $modelClass);

        if (! $this->filesystem->exists($modelFilePath)) {
            $files[] = new PlannedFile(
                path: $modelFilePath,
                contents: $this->templateRenderer->render(
                    base_path('stubs/module-generation/backend/model.stub'),
                    $modelTokens,
                ),
            );
        }

        $existingMigrationFiles = glob($migrationsPath.sprintf('/*_create_%s_table.php', $tableName));
        $existingMigrationFiles = is_array($existingMigrationFiles) ? $existingMigrationFiles : [];

        if ($existingMigrationFiles === []) {
            $files[] = new PlannedFile(
                path: sprintf('%s/%s', $migrationsPath, $migrationFileName),
                contents: $this->templateRenderer->render(
                    base_path('stubs/module-generation/backend/migration.stub'),
                    $migrationTokens,
                ),
            );
        }
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendFrontendPageScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        if ($generateModuleInput->generateCrud) {
            $this->appendFrontendCrudPageScaffold($generateModuleInput, $directories, $files);

            return;
        }

        $this->appendFrontendSimplePageScaffold($generateModuleInput, $directories, $files);
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendFrontendSimplePageScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $moduleFrontendRootPath = $this->moduleFrontendRootPath($generateModuleInput);
        $formsPath = $moduleFrontendRootPath.'/forms';
        $pagesPath = $moduleFrontendRootPath.'/pages';
        $pageTestsPath = $pagesPath.'/__tests__';

        $directories[] = $formsPath;
        $directories[] = $pagesPath;
        $directories[] = $pageTestsPath;

        $pagePascalName = $generateModuleInput->pagePascalName;
        $pageKebabName = $generateModuleInput->pageKebabName;
        $pageCamelName = lcfirst($pagePascalName);

        $crudResourceManifest = CrudResourceManifest::fromGenerateModuleInput($generateModuleInput);
        $schemaTokens = [
            'pagePascalName' => $pagePascalName,
            'pageCamelName' => $pageCamelName,
            'formTypeImports' => '',
            'formValuesType' => "{\n".$this->renderFormValueFields($crudResourceManifest)."\n}",
            'formDefaultValues' => $this->renderFormDefaultValues($crudResourceManifest),
            'formFieldDefinitions' => $this->renderFormFieldDefinitions($crudResourceManifest),
        ];

        $pageTokens = [
            ...$this->frontendLayoutTokens($generateModuleInput),
            'pagePascalName' => $pagePascalName,
            'pageCamelName' => $pageCamelName,
            'pageKebabName' => $pageKebabName,
        ];

        $pageTestTokens = [
            'pagePascalName' => $pagePascalName,
            'pageKebabName' => $pageKebabName,
        ];

        $files[] = new PlannedFile(
            path: sprintf('%s/%s-form-schema.ts', $formsPath, $pageKebabName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/form-schema.stub'),
                $schemaTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%s.vue', $pagesPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/page.stub'),
                $pageTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%s.test.ts', $pageTestsPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/page-test.stub'),
                $pageTestTokens,
            ),
        );
    }

    private function requiresWebAuthentication(GenerateModuleInput $generateModuleInput): bool
    {
        return array_any($generateModuleInput->middleware, static fn (string $middleware): bool => $middleware === 'auth' || str_starts_with($middleware, 'auth:'));
    }

    /** @return array<string, string> */
    private function frontendLayoutTokens(GenerateModuleInput $generateModuleInput): array
    {
        $authenticated = $this->requiresWebAuthentication($generateModuleInput);

        return [
            'pageLayout' => $authenticated ? 'AppLayout' : 'MarketingPageLayout',
            'pageLayoutAttributes' => $authenticated ? ' :breadcrumbs="breadcrumbs"' : '',
            'pageBreadcrumbs' => $authenticated ? 'const breadcrumbs = buildDashboardBreadcrumbs()' : '',
        ];
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendFrontendCrudPageScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $moduleFrontendRootPath = $this->moduleFrontendRootPath($generateModuleInput);
        $crudResourceManifest = $this->crudResourceManifest($generateModuleInput);
        $contractsPath = $moduleFrontendRootPath.'/contracts';
        $formsPath = $moduleFrontendRootPath.'/forms';
        $componentsPath = $moduleFrontendRootPath.'/components';
        $pagesPath = $moduleFrontendRootPath.'/pages';
        $pageTestsPath = $pagesPath.'/__tests__';

        $directories[] = $contractsPath;
        $directories[] = $formsPath;
        $directories[] = $componentsPath;
        $directories[] = $pagesPath;
        $directories[] = $pageTestsPath;

        $pagePascalName = $generateModuleInput->pagePascalName;
        $pageKebabName = $generateModuleInput->pageKebabName;
        $moduleComponentPrefix = implode('', $generateModuleInput->moduleName->namespaceSegments);
        $controllerActionImportPath = sprintf(
            '@/actions/App/Modules/%s/Http/Controllers/%sController',
            $generateModuleInput->moduleName->path,
            $pagePascalName,
        );

        $commonTokens = [
            ...$this->frontendLayoutTokens($generateModuleInput),
            'pagePascalName' => $pagePascalName,
            'pageKebabName' => $pageKebabName,
            'moduleComponentPrefix' => $moduleComponentPrefix,
            'controllerActionImportPath' => $controllerActionImportPath,
            'modelVariable' => $this->modelVariableName($generateModuleInput->moduleName),
            'moduleKebab' => $generateModuleInput->moduleName->frontendKebab,
            'moduleNavigationTitle' => $this->moduleNavigationTitle($generateModuleInput->moduleName->frontendKebab),
            'dashboardNavHrefExpression' => $this->dashboardNavHrefExpression($generateModuleInput->routeNamePrefix),
            'pageDataClass' => $this->pageDataClassName($generateModuleInput),
            'listItemDataClass' => $this->listItemDataClassName($generateModuleInput),
            'tableColumnDefinitions' => $this->renderTableColumnDefinitions($crudResourceManifest),
            'mobileFieldDefinitions' => $this->renderMobileFieldDefinitions($crudResourceManifest),
            'tablePrimaryFieldKey' => $this->tablePrimaryFieldKey($crudResourceManifest),
        ];

        $schemaTokens = [
            'pagePascalName' => $pagePascalName,
            'pageCamelName' => lcfirst($pagePascalName),
            'formTypeImports' => sprintf("import type { FormValuesFromData } from '@/lib/forms'\nimport type { %s } from '@/types/app-data'", $this->storeDataClassName($generateModuleInput)),
            'formValuesType' => sprintf('FormValuesFromData<%s>', $this->storeDataClassName($generateModuleInput)),
            'formDefaultValues' => $this->renderFormDefaultValues($crudResourceManifest),
            'formFieldDefinitions' => $this->renderFormFieldDefinitions($crudResourceManifest),
        ];

        $pageTestTokens = [
            'pagePascalName' => $pagePascalName,
            'pageKebabName' => $pageKebabName,
        ];

        $files[] = new PlannedFile(
            path: sprintf('%s/%s-crud.ts', $contractsPath, $pageKebabName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/crud-contract.stub'),
                $commonTokens,
            ),
        );

        if ($generateModuleInput->routeProfile === RouteProfile::APP) {
            $dashboardNavTokens = array_merge($commonTokens, [
                'dashboardNavRoleImportLine' => $generateModuleInput->allowedRoles === []
                    ? ''
                    : "import { UserRole } from '@/types/app-data'\n",
                'dashboardNavRoles' => $this->dashboardNavRolesExpression($generateModuleInput->allowedRoles),
            ]);

            $files[] = new PlannedFile(
                path: sprintf('%s/dashboard-nav.ts', $contractsPath),
                contents: $this->templateRenderer->render(
                    base_path('stubs/module-generation/frontend/dashboard-nav.stub'),
                    $dashboardNavTokens,
                ),
            );
        }

        $files[] = new PlannedFile(
            path: sprintf('%s/%s-form-schema.ts', $formsPath, $pageKebabName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/form-schema.stub'),
                $schemaTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/Table.vue', $componentsPath),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/crud-table.stub'),
                $commonTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%sFormDialog.vue', $componentsPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/crud-form-dialog.stub'),
                $commonTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%sDeleteDialog.vue', $componentsPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/crud-delete-dialog.stub'),
                $commonTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%sDetailsDialog.vue', $componentsPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/crud-details-dialog.stub'),
                $commonTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%s.vue', $pagesPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/crud-page.stub'),
                $commonTokens,
            ),
        );

        $files[] = new PlannedFile(
            path: sprintf('%s/%s.test.ts', $pageTestsPath, $pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/frontend/page-test.stub'),
                $pageTestTokens,
            ),
        );
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendFeatureTestScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $testsDirectory = $generateModuleInput->basePath.'/tests/Feature/'.$generateModuleInput->moduleName->path;
        $directories[] = $testsDirectory;

        $routeUri = $generateModuleInput->routePrefix === '' ? '/' : '/'.$generateModuleInput->routePrefix;
        $routeLabel = str_replace('-', ' ', $generateModuleInput->moduleName->frontendKebab);
        $guestAssertion = $this->requiresWebAuthentication($generateModuleInput)
            ? "\$testResponse->assertRedirect('/auth/login');"
            : '$testResponse->assertOk();';
        $restrictedByRoles = $this->isRoleRestricted($generateModuleInput);
        $deniedRoleCase = $restrictedByRoles ? $this->deniedRoleCase($generateModuleInput->allowedRoles) : null;

        $tokens = [
            'moduleNamespace' => $generateModuleInput->moduleName->namespace,
            'pagePascalName' => $generateModuleInput->pagePascalName,
            'moduleKebab' => $generateModuleInput->moduleName->frontendKebab,
            'routeUri' => $routeUri,
            'routeLabel' => $routeLabel,
            'guestAssertion' => $guestAssertion,
            'userRoleImportLine' => $restrictedByRoles ? "use App\\Enums\\UserRole;\n" : '',
            'forbiddenRoleTest' => $deniedRoleCase !== null
                ? $this->forbiddenRoleTest($routeLabel, $routeUri, $deniedRoleCase)
                : '',
            'allowedRoleFactoryState' => $this->allowedRoleFactoryState($generateModuleInput->allowedRoles),
        ];

        $files[] = new PlannedFile(
            path: sprintf('%s/%sPageTest.php', $testsDirectory, $generateModuleInput->pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/tests/feature-page-test.stub'),
                $tokens,
            ),
        );
    }

    private function shouldGenerateRoleGateFile(GenerateModuleInput $generateModuleInput): bool
    {
        return $this->isRoleRestricted($generateModuleInput)
            && (
                ($generateModuleInput->generateCrud && $generateModuleInput->routeProfile === RouteProfile::APP)
                || ($generateModuleInput->generateApi && $generateModuleInput->apiRouteProfile === ApiRouteProfile::PROTECTED)
            );
    }

    private function isRoleRestricted(GenerateModuleInput $generateModuleInput): bool
    {
        return $generateModuleInput->allowedRoles !== [];
    }

    /**
     * @param  array<int, string>  $directories
     * @param  array<int, PlannedFile>  $files
     */
    private function appendApiFeatureTestScaffold(GenerateModuleInput $generateModuleInput, array &$directories, array &$files): void
    {
        $testsDirectory = $generateModuleInput->basePath.'/tests/Feature/'.$generateModuleInput->moduleName->path;
        $directories[] = $testsDirectory;

        $apiRouteUri = $generateModuleInput->apiRoutePrefix === ''
            ? '/api'
            : '/api/'.$generateModuleInput->apiRoutePrefix;
        $routeLabel = str_replace('-', ' ', $generateModuleInput->moduleName->frontendKebab);
        $apiRequiresAuth = in_array('auth:sanctum', $generateModuleInput->apiMiddleware, true);

        $apiGuestAssertion = $apiRequiresAuth
            ? '$testResponse->assertUnauthorized();'
            : '$testResponse->assertOk();';
        $allowedRoleFactoryState = $this->allowedRoleFactoryState($generateModuleInput->allowedRoles);

        $apiAuthRequestLine = $apiRequiresAuth
            ? "\$user = User::factory()->create({$allowedRoleFactoryState});\n\n    \$testResponse = \$this->actingAs(\$user)->getJson('{$apiRouteUri}');"
            : sprintf("\$testResponse = \$this->getJson('%s');", $apiRouteUri);

        $apiAuthFollowUpLine = $apiRequiresAuth
            ? sprintf("\$this->actingAs(\$user)->getJson('%s')", $apiRouteUri)
            : sprintf("\$this->getJson('%s')", $apiRouteUri);
        $restrictedByRoles = $this->isRoleRestricted($generateModuleInput)
            && $generateModuleInput->apiRouteProfile === ApiRouteProfile::PROTECTED;
        $deniedRoleCase = $restrictedByRoles ? $this->deniedRoleCase($generateModuleInput->allowedRoles) : null;

        $tokens = [
            'moduleNamespace' => $generateModuleInput->moduleName->namespace,
            'pagePascalName' => $generateModuleInput->pagePascalName,
            'modelClass' => $this->modelClassName($generateModuleInput->moduleName),
            'routeLabel' => $routeLabel,
            'apiRouteUri' => $apiRouteUri,
            'apiGuestAssertion' => $apiGuestAssertion,
            'apiAuthRequestLine' => $apiAuthRequestLine,
            'apiAuthFollowUpLine' => $apiAuthFollowUpLine,
            'userRoleImportLine' => $restrictedByRoles ? "use App\\Enums\\UserRole;\n" : '',
            // A public API scaffold never authenticates, so importing the user model
            // there would leave the generated test with an unused import.
            'userModelImportLine' => $apiRequiresAuth || $deniedRoleCase !== null ? "use App\\Models\\User;\n" : '',
            'forbiddenRoleTest' => $deniedRoleCase !== null
                ? $this->forbiddenApiRoleTest($routeLabel, $apiRouteUri, $deniedRoleCase)
                : '',
        ];

        $files[] = new PlannedFile(
            path: sprintf('%s/%sApiTest.php', $testsDirectory, $generateModuleInput->pagePascalName),
            contents: $this->templateRenderer->render(
                base_path('stubs/module-generation/tests/feature-api-test.stub'),
                $tokens,
            ),
        );
    }

    /**
     * @param  array<int|string, string>  $middleware
     */
    private function buildMiddlewareLine(array $middleware): string
    {
        if ($middleware === []) {
            return '';
        }

        $quoted = array_map(
            static fn (string $middlewareItem): string => sprintf("'%s'", $middlewareItem),
            $middleware,
        );

        return sprintf("    ->middleware([%s])\n", implode(', ', $quoted));
    }

    private function shouldGenerateHandlers(GenerateModuleInput $generateModuleInput): bool
    {
        return $generateModuleInput->generateCrud && $generateModuleInput->generateApi;
    }

    private function crudResourceManifest(GenerateModuleInput $generateModuleInput): CrudResourceManifest
    {
        return $generateModuleInput->crudResourceManifest
            ?? CrudResourceManifest::fromGenerateModuleInput($generateModuleInput);
    }

    private function moduleRootPath(GenerateModuleInput $generateModuleInput): string
    {
        return $generateModuleInput->basePath.'/app/Modules/'.$generateModuleInput->moduleName->path;
    }

    private function moduleFrontendRootPath(GenerateModuleInput $generateModuleInput): string
    {
        return $generateModuleInput->basePath.'/resources/js/modules/'.$generateModuleInput->moduleName->frontendKebab;
    }

    private function modelClassName(ModuleName $moduleName): string
    {
        return implode('', $moduleName->namespaceSegments);
    }

    private function modelVariableName(ModuleName $moduleName): string
    {
        return Str::camel($this->modelClassName($moduleName));
    }

    private function pageDataClassName(GenerateModuleInput $generateModuleInput): string
    {
        return $this->modelClassName($generateModuleInput->moduleName).$generateModuleInput->pagePascalName.'PageData';
    }

    private function listItemDataClassName(GenerateModuleInput $generateModuleInput): string
    {
        return $this->modelClassName($generateModuleInput->moduleName).$generateModuleInput->pagePascalName.'ListItemData';
    }

    private function storeDataClassName(GenerateModuleInput $generateModuleInput): string
    {
        return $this->modelClassName($generateModuleInput->moduleName).$generateModuleInput->pagePascalName.'StoreData';
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

    /**
     * @param  array<int, PlannedFile>  $files
     * @return list<PlannedFile>
     */
    private function uniquePlannedFiles(array $files): array
    {
        $uniqueFiles = [];

        foreach ($files as $file) {
            $uniqueFiles[$file->path] = $file;
        }

        return array_values($uniqueFiles);
    }

    /**
     * @param  list<string>  $allowedRoles
     * @return list<string>
     */
    private function allowedPhpRoleCases(array $allowedRoles): array
    {
        $roleCaseLookup = [];

        foreach (UserRole::cases() as $role) {
            $roleCaseLookup[$role->value] = 'UserRole::'.$role->name;
        }

        $cases = [];

        foreach ($allowedRoles as $allowedRole) {
            if (isset($roleCaseLookup[$allowedRole])) {
                $cases[] = $roleCaseLookup[$allowedRole];
            }
        }

        return $cases;
    }

    /**
     * @param  list<string>  $allowedRoles
     * @return list<string>
     */
    private function allowedTsRoleCases(array $allowedRoles): array
    {
        $roleCaseLookup = [];

        foreach (UserRole::cases() as $role) {
            $roleCaseLookup[$role->value] = 'UserRole.'.$role->name;
        }

        $cases = [];

        foreach ($allowedRoles as $allowedRole) {
            if (isset($roleCaseLookup[$allowedRole])) {
                $cases[] = $roleCaseLookup[$allowedRole];
            }
        }

        return $cases;
    }

    /**
     * @param  list<string>  $allowedRoles
     */
    private function dashboardNavRolesExpression(array $allowedRoles): string
    {
        if ($allowedRoles === []) {
            return "'all'";
        }

        return '['.implode(', ', $this->allowedTsRoleCases($allowedRoles)).']';
    }

    private function moduleNavigationTitle(string $moduleKebab): string
    {
        return Str::title(str_replace('-', ' ', $moduleKebab));
    }

    private function dashboardNavHrefExpression(string $routeNamePrefix): string
    {
        $segments = array_values(array_filter(explode('.', $routeNamePrefix), static fn (string $segment): bool => $segment !== ''));

        if ($segments !== [] && $segments[0] === 'app') {
            array_shift($segments);
        }

        $segments[] = 'index';
        $segments[] = 'url()';

        return 'appRoutes'.$this->renderJsPropertyPath($segments);
    }

    /**
     * @param  list<string>  $segments
     */
    private function renderJsPropertyPath(array $segments): string
    {
        $path = '';

        foreach ($segments as $segment) {
            if ($segment === 'url()') {
                $path .= '.url()';

                continue;
            }

            $property = Str::camel($segment);

            if ($property !== '' && preg_match('/^[A-Za-z_$][A-Za-z0-9_$]*$/', $property) === 1) {
                $path .= '.'.$property;

                continue;
            }

            $path .= '['.var_export($property, true).']';
        }

        return $path;
    }

    /**
     * @param  list<string>  $allowedRoles
     */
    private function allowedRoleFactoryState(array $allowedRoles): string
    {
        if ($allowedRoles === []) {
            return '';
        }

        $firstAllowedRoleCase = $this->allowedPhpRoleCases($allowedRoles)[0] ?? null;

        if ($firstAllowedRoleCase === null) {
            return '';
        }

        return sprintf("['role' => %s]", $firstAllowedRoleCase);
    }

    /**
     * @param  list<string>  $allowedRoles
     */
    private function deniedRoleCase(array $allowedRoles): ?string
    {
        $allowedRoleLookup = array_fill_keys($allowedRoles, true);

        foreach (UserRole::cases() as $role) {
            if (! isset($allowedRoleLookup[$role->value])) {
                return 'UserRole::'.$role->name;
            }
        }

        return null;
    }

    private function forbiddenRoleTest(string $routeLabel, string $routeUri, string $deniedRoleCase): string
    {
        return sprintf(
            "test('users without required role cannot visit %s page', function (): void {\n".
            "    \$user = User::factory()->create(['role' => %s]);\n\n".
            "    \$testResponse = \$this->actingAs(\$user)->get('%s');\n\n".
            "    \$testResponse->assertForbidden();\n".
            "});\n\n",
            $routeLabel,
            $deniedRoleCase,
            $routeUri,
        );
    }

    private function forbiddenApiRoleTest(string $routeLabel, string $routeUri, string $deniedRoleCase): string
    {
        return sprintf(
            "test('users without required role cannot list %s api results', function (): void {\n".
            "    \$user = User::factory()->create(['role' => %s]);\n\n".
            "    \$testResponse = \$this->actingAs(\$user)->getJson('%s');\n\n".
            "    \$testResponse->assertForbidden();\n".
            "});\n\n",
            $routeLabel,
            $deniedRoleCase,
            $routeUri,
        );
    }

    /**
     * @param  list<string>  $values
     */
    private function renderPhpStringList(array $values): string
    {
        if ($values === []) {
            return '';
        }

        return implode(', ', array_map(
            static fn (string $value): string => var_export($value, true),
            $values,
        ));
    }

    private function renderPhpBoolean(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    private function renderManifestTableColumns(CrudResourceManifest $crudResourceManifest): string
    {
        return implode("\n", array_map(
            fn (array $column): string => sprintf(
                "            ['key' => %s, 'label' => %s, 'type' => %s, 'sortable' => %s],",
                var_export($column['key'], true),
                var_export($column['label'], true),
                var_export($column['type'], true),
                $this->renderPhpBoolean($column['sortable']),
            ),
            $crudResourceManifest->tableColumns,
        ));
    }

    private function renderManifestMobileFields(CrudResourceManifest $crudResourceManifest): string
    {
        return implode("\n", array_map(function (array $mobileField): string {
            $parts = [
                sprintf("'key' => %s", var_export($mobileField['key'], true)),
                sprintf("'label' => %s", var_export($mobileField['label'], true)),
                sprintf("'type' => %s", var_export($mobileField['type'], true)),
            ];

            if (isset($mobileField['class'])) {
                $parts[] = sprintf("'class' => %s", var_export($mobileField['class'], true));
            }

            return sprintf('            [%s],', implode(', ', $parts));
        }, $crudResourceManifest->mobileFields));
    }

    private function renderManifestFormFields(CrudResourceManifest $crudResourceManifest): string
    {
        return implode("\n", array_map(function (array $formField): string {
            $parts = [
                sprintf("'name' => %s", var_export($formField['name'], true)),
                sprintf("'label' => %s", var_export($formField['label'], true)),
                sprintf("'type' => %s", var_export($formField['type'], true)),
                sprintf("'required' => %s", $this->renderPhpBoolean($formField['required'])),
            ];

            if (isset($formField['placeholder'])) {
                $parts[] = sprintf("'placeholder' => %s", var_export($formField['placeholder'], true));
            }

            if (isset($formField['autocomplete'])) {
                $parts[] = sprintf("'autocomplete' => %s", var_export($formField['autocomplete'], true));
            }

            return sprintf('            [%s],', implode(', ', $parts));
        }, $crudResourceManifest->formFields));
    }

    private function renderFormValueFields(CrudResourceManifest $crudResourceManifest): string
    {
        return implode("\n", array_map(
            fn (array $formField): string => sprintf('    %s: %s', $formField['name'], $this->formFieldType($formField['type'])),
            $crudResourceManifest->formFields,
        ));
    }

    private function renderFormDefaultValues(CrudResourceManifest $crudResourceManifest): string
    {
        return implode(",\n", array_map(
            fn (array $formField): string => sprintf('        %s: %s', $formField['name'], $this->formFieldDefaultValue($formField['type'])),
            $crudResourceManifest->formFields,
        ));
    }

    private function renderFormFieldDefinitions(CrudResourceManifest $crudResourceManifest): string
    {
        return implode(",\n", array_map(function (array $formField): string {
            // Properties are joined rather than individually terminated so the last one
            // carries no comma, matching Prettier's `trailingComma: none`.
            $properties = [
                sprintf('                name: %s', $this->renderTsString($formField['name'])),
                sprintf('                label: %s', $this->renderTsString($formField['label'])),
                sprintf('                type: %s', $this->renderTsString($formField['type'])),
                sprintf('                required: %s', $formField['required'] ? 'true' : 'false'),
            ];

            if (isset($formField['placeholder'])) {
                $properties[] = sprintf('                placeholder: %s', $this->renderTsString($formField['placeholder']));
            }

            if (isset($formField['autocomplete'])) {
                $properties[] = sprintf('                autocomplete: %s', $this->renderTsString($formField['autocomplete']));
            }

            return "            {\n".implode(",\n", $properties)."\n            }";
        }, $crudResourceManifest->formFields));
    }

    private function renderTableColumnDefinitions(CrudResourceManifest $crudResourceManifest): string
    {
        return implode(",\n", array_map(function (array $column): string {
            $valueExpression = $column['type'] === 'date'
                ? sprintf('formatDate(row.%s)', $column['key'])
                : sprintf('row.%s', $column['key']);

            return implode("\n", [
                '        {',
                sprintf('            key: %s,', $this->renderTsString($column['key'])),
                sprintf('            label: %s,', $this->renderTsString($column['label'])),
                sprintf('            sortable: %s,', $column['sortable'] ? 'true' : 'false'),
                sprintf('            value: (row) => %s', $valueExpression),
                '        }',
            ]);
        }, $crudResourceManifest->tableColumns));
    }

    private function renderMobileFieldDefinitions(CrudResourceManifest $crudResourceManifest): string
    {
        return implode(",\n", array_map(function (array $mobileField): string {
            $valueExpression = $mobileField['type'] === 'date'
                ? sprintf('formatDate(row.%s)', $mobileField['key'])
                : sprintf('row.%s', $mobileField['key']);

            $lines = [
                '        {',
                sprintf('            key: %s,', $this->renderTsString($mobileField['key'])),
                sprintf('            label: %s,', $this->renderTsString($mobileField['label'])),
            ];

            if (isset($mobileField['class'])) {
                $lines[] = sprintf('            class: %s,', $this->renderTsString($mobileField['class']));
            }

            $lines[] = sprintf('            value: (row) => %s', $valueExpression);
            $lines[] = '        }';

            return implode("\n", $lines);
        }, $crudResourceManifest->mobileFields));
    }

    private function tablePrimaryFieldKey(CrudResourceManifest $crudResourceManifest): string
    {
        foreach ($crudResourceManifest->tableColumns as $column) {
            if ($column['type'] === 'text') {
                return $column['key'];
            }
        }

        return $crudResourceManifest->tableColumns[0]['key'];
    }

    private function formFieldType(string $type): string
    {
        return match ($type) {
            'checkbox' => 'boolean',
            default => 'string',
        };
    }

    private function formFieldDefaultValue(string $type): string
    {
        return match ($type) {
            'checkbox' => 'false',
            default => "''",
        };
    }

    private function renderTsString(string $value): string
    {
        return var_export($value, true);
    }
}
