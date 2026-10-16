<?php

declare(strict_types=1);

use App\Modules\Shared\Support\ModuleGeneration\TemplateRenderer;

test('derived names that collide with generated php imports fail before writing files', function (string $module): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();
    $this->runGenerateCommand([
        'module' => $module,
        '--scaffold' => 'crud-api',
        '--route-profile' => 'public',
        '--api-route-profile' => 'public',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(1);

    expect($basePath.'/app/Modules/'.$module)->not->toBeDirectory();
    expect($basePath.'/app/Models/'.$module.'.php')->not->toBeFile();
})->with(['Model', 'Factory', 'RuntimeException', 'PaginationQueryData', 'JsonResponse', 'Controller']);

test('frontend layouts follow route authentication for every page scaffold', function (string $scaffold, string $profile, string $middleware, string $layout): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();
    $this->runGenerateCommand([
        'module' => 'Guestcatalog',
        '--scaffold' => $scaffold,
        '--route-profile' => $profile,
        '--route-prefix' => 'guestcatalog',
        '--route-name-prefix' => 'guestcatalog',
        '--middleware' => $middleware,
        '--roles' => 'all',
        '--no-interaction' => true,
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(0);

    $contents = (string) file_get_contents($basePath.'/resources/js/modules/guestcatalog/pages/Index.vue');
    expect($contents)->toContain('<'.$layout)->toContain('</'.$layout.'>');
    if ($layout === 'MarketingPageLayout') {
        expect($contents)->not->toContain('AppLayout')->not->toContain('buildDashboardBreadcrumbs');
    }
})->with(['page', 'crud', 'crud-api'])->with([
    ['public', '', 'MarketingPageLayout'],
    ['app', 'auth,verified', 'AppLayout'],
    ['custom', 'guest', 'MarketingPageLayout'],
    ['custom', '', 'AppLayout'],
    ['custom', 'auth:web', 'AppLayout'],
]);

test('unresolved template tokens fail before scaffolding writes files', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();
    $stub = $basePath.'/missing.stub';
    file_put_contents($stub, '{{ supplied }} {{ missing }}');
    expect(fn () => app(TemplateRenderer::class)->render($stub, ['supplied' => 'value']))
        ->toThrow(RuntimeException::class, 'Missing template token "missing"');
});

test('invalid namespace page and model names are rejected before writing files', function (string $module, string $page): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();
    $this->runGenerateCommand([
        'module' => $module,
        '--page' => $page,
        '--scaffold' => 'crud',
        '--route-profile' => 'public',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(1);
    expect(glob($basePath.'/app/Modules/*'))->toBe([]);
})->with([['123Billing', 'Index'], ['Billing/123Invoices', 'Index'], ['Billing', '123Index'], ['Class', 'Index'], ['String', 'Index'], ['Match', 'Index']]);

test('fresh crud mode scaffolds backend frontend and tests', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--scaffold' => 'crud',
        '--page' => 'Index',
        '--route-profile' => 'app',
        '--roles' => 'all',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(0);

    expect($basePath.'/app/Modules/Billing/Http/Controllers/IndexController.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Http/Requests/IndexStoreRequest.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Http/Requests/IndexUpdateRequest.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Data/BillingIndexStoreData.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Data/BillingIndexListItemData.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Data/BillingIndexPageData.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Manifests/IndexResource.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Queries/BillingQueries.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Commands/BillingCommands.php')->toBeFile();
    $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Handlers/BillingQueryHandler.php');
    $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Handlers/BillingCommandHandler.php');
    $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Services/IndexService.php');
    expect($basePath.'/app/Modules/Billing/Routes/web.php')->toBeFile();
    $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Routes/api.php');
    expect($basePath.'/app/Models/Billing.php')->toBeFile();
    expect($basePath.'/resources/js/modules/billing/contracts/index-crud.ts')->toBeFile();
    expect($basePath.'/resources/js/modules/billing/contracts/dashboard-nav.ts')->toBeFile();
    expect($basePath.'/resources/js/modules/billing/forms/index-form-schema.ts')->toBeFile();
    expect($basePath.'/resources/js/modules/billing/components/Table.vue')->toBeFile();
    expect($basePath.'/resources/js/modules/billing/components/IndexFormDialog.vue')->toBeFile();
    expect($basePath.'/resources/js/modules/billing/components/IndexDeleteDialog.vue')->toBeFile();
    expect($basePath.'/resources/js/modules/billing/components/IndexDetailsDialog.vue')->toBeFile();
    expect($basePath.'/resources/js/modules/billing/pages/Index.vue')->toBeFile();
    expect($basePath.'/resources/js/modules/billing/pages/__tests__/Index.test.ts')->toBeFile();
    expect($basePath.'/tests/Feature/Billing/IndexPageTest.php')->toBeFile();
    $this->assertFileDoesNotExist($basePath.'/tests/Feature/Billing/IndexApiTest.php');
    $this->assertMigrationFileExists($basePath, 'billings');

    $pageFeatureTestContents = file_get_contents($basePath.'/tests/Feature/Billing/IndexPageTest.php');
    $pageFeatureTestContents = is_string($pageFeatureTestContents) ? $pageFeatureTestContents : '';
    $this->assertStringContainsString('uses(RefreshDatabase::class);', $pageFeatureTestContents);
    $this->assertStringContainsString("test('guests access behavior for billing page'", $pageFeatureTestContents);
    $this->assertStringNotContainsString('extends TestCase', $pageFeatureTestContents);

    $requestFileContents = file_get_contents($basePath.'/app/Modules/Billing/Http/Requests/IndexStoreRequest.php');
    $requestFileContents = is_string($requestFileContents) ? $requestFileContents : '';

    $this->assertStringContainsString('extends DataFormRequest', $requestFileContents);
    $this->assertStringContainsString('return BillingIndexStoreData::class;', $requestFileContents);

    $modelFileContents = file_get_contents($basePath.'/app/Models/Billing.php');
    $modelFileContents = is_string($modelFileContents) ? $modelFileContents : '';
    $this->assertStringContainsString('/** @use HasFactory<Factory<self>> */', $modelFileContents);

    $queryFileContents = file_get_contents($basePath.'/app/Modules/Billing/Queries/BillingQueries.php');
    $queryFileContents = is_string($queryFileContents) ? $queryFileContents : '';
    $this->assertStringContainsString('@return LengthAwarePaginator<int, Billing>', $queryFileContents);

    $controllerFileContents = file_get_contents($basePath.'/app/Modules/Billing/Http/Controllers/IndexController.php');
    $controllerFileContents = is_string($controllerFileContents) ? $controllerFileContents : '';
    $this->assertStringContainsString('use App\\Modules\\Billing\\Data\\BillingIndexPageData;', $controllerFileContents);
    $this->assertStringContainsString('use App\\Modules\\Shared\\Http\\Responders\\PageResponder;', $controllerFileContents);
    $this->assertStringContainsString('PageResponder::render(', $controllerFileContents);
    $this->assertStringContainsString('BillingIndexPageData::fromPaginator($lengthAwarePaginator)', $controllerFileContents);

    $pageDataContents = file_get_contents($basePath.'/app/Modules/Billing/Data/BillingIndexPageData.php');
    $pageDataContents = is_string($pageDataContents) ? $pageDataContents : '';
    $this->assertStringContainsString('final class BillingIndexPageData extends Data', $pageDataContents);
    $this->assertStringContainsString('final class BillingIndexListItemData extends Data', (string) file_get_contents($basePath.'/app/Modules/Billing/Data/BillingIndexListItemData.php'));

    $manifestContents = file_get_contents($basePath.'/app/Modules/Billing/Manifests/IndexResource.php');
    $manifestContents = is_string($manifestContents) ? $manifestContents : '';
    $this->assertStringContainsString("'profile' => 'app'", $manifestContents);
    $this->assertStringContainsString("'roles' => []", $manifestContents);
    $this->assertStringContainsString("'columns' => [", $manifestContents);
    $this->assertStringContainsString("'fields' => [", $manifestContents);
    $this->assertStringContainsString("'enabled' => false", $manifestContents);

    $routeFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/web.php');
    $routeFileContents = is_string($routeFileContents) ? $routeFileContents : '';

    $this->assertStringContainsString("Route::prefix('app/billing')", $routeFileContents);
    $this->assertStringContainsString("->as('app.billing.')", $routeFileContents);
    $this->assertStringContainsString("Route::put('/{billing}'", $routeFileContents);
    $this->assertStringNotContainsString('can:manage-billing', $routeFileContents);
    $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Routes/gates.php');

    $pageFileContents = file_get_contents($basePath.'/resources/js/modules/billing/pages/Index.vue');
    $pageFileContents = is_string($pageFileContents) ? $pageFileContents : '';

    $this->assertStringContainsString("import type { BillingIndexPageData } from '@/types/app-data'", $pageFileContents);
    $this->assertStringContainsString("import type { IndexListItem } from '../contracts/index-crud'", $pageFileContents);
    $this->assertStringContainsString('<BillingTable', $pageFileContents);
    $this->assertStringContainsString('<BillingIndexFormDialog', $pageFileContents);

    $crudContractContents = file_get_contents($basePath.'/resources/js/modules/billing/contracts/index-crud.ts');
    $crudContractContents = is_string($crudContractContents) ? $crudContractContents : '';
    $this->assertStringContainsString("import type { BillingIndexListItemData } from '@/types/app-data'", $crudContractContents);
    $this->assertStringContainsString('export type IndexListItem = BillingIndexListItemData', $crudContractContents);

    $tableFileContents = file_get_contents($basePath.'/resources/js/modules/billing/components/Table.vue');
    $tableFileContents = is_string($tableFileContents) ? $tableFileContents : '';
    $this->assertStringContainsString("import type { IndexListItem } from '../contracts/index-crud'", $tableFileContents);
    $this->assertStringContainsString('const formatDate = (value: string | null | undefined): string => {', $tableFileContents);
    $this->assertStringContainsString('const rowKey = (row: IndexListItem): number => row.id', $tableFileContents);
    $this->assertStringContainsString(':row-key="rowKey"', $tableFileContents);
    $this->assertStringContainsString("key: 'created_at'", $tableFileContents);
    $this->assertStringContainsString('formatDate(row.created_at)', $tableFileContents);

    $formSchemaContents = file_get_contents($basePath.'/resources/js/modules/billing/forms/index-form-schema.ts');
    $formSchemaContents = is_string($formSchemaContents) ? $formSchemaContents : '';
    $this->assertStringContainsString("import type { BillingIndexStoreData } from '@/types/app-data'", $formSchemaContents);
    $this->assertStringContainsString('export type IndexFormValues = FormValuesFromData<BillingIndexStoreData>', $formSchemaContents);
    $this->assertStringContainsString("placeholder: 'Enter name'", $formSchemaContents);

    $formDialogContents = file_get_contents($basePath.'/resources/js/modules/billing/components/IndexFormDialog.vue');
    $formDialogContents = is_string($formDialogContents) ? $formDialogContents : '';
    $this->assertStringContainsString("import type { IndexListItem } from '../contracts/index-crud'", $formDialogContents);
    $this->assertStringContainsString("import { buildIndexFormFields, createIndexFormDefaults, type IndexFormValues } from '../forms/index-form-schema'", $formDialogContents);

    $dashboardNavContents = file_get_contents($basePath.'/resources/js/modules/billing/contracts/dashboard-nav.ts');
    $dashboardNavContents = is_string($dashboardNavContents) ? $dashboardNavContents : '';
    $this->assertStringContainsString('href: appRoutes.billing.index.url()', $dashboardNavContents);
    $this->assertStringContainsString("import IconLucideShapes from '~icons/lucide/shapes'", $dashboardNavContents);
    $this->assertStringContainsString('icon: IconLucideShapes', $dashboardNavContents);

    $crudPageContents = file_get_contents($basePath.'/resources/js/modules/billing/pages/Index.vue');
    $crudPageContents = is_string($crudPageContents) ? $crudPageContents : '';
    $this->assertStringContainsString("import IconLucidePlus from '~icons/lucide/plus'", $crudPageContents);
    $this->assertStringContainsString(':icon-left="IconLucidePlus"', $crudPageContents);
    $this->assertStringContainsString('const theme = appTheme', $crudPageContents);
    $this->assertStringContainsString(':class="theme.layout.pageHeader"', $crudPageContents);
    $this->assertStringNotContainsString('sm:flex-row sm:items-center sm:justify-between', $crudPageContents);
});
test('api mode scaffolds api assets and skips frontend assets', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--scaffold' => 'api',
        '--api-route-profile' => 'protected',
        '--roles' => 'admin',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(0);

    expect($basePath.'/app/Modules/Billing/Http/Controllers/IndexApiController.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Http/Resources/IndexResource.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Http/Requests/IndexStoreRequest.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Http/Requests/IndexUpdateRequest.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Data/BillingIndexStoreData.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Queries/BillingQueries.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Commands/BillingCommands.php')->toBeFile();
    $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Handlers/BillingQueryHandler.php');
    $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Handlers/BillingCommandHandler.php');
    $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Services/IndexService.php');
    expect($basePath.'/app/Modules/Billing/Routes/api.php')->toBeFile();
    $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Routes/web.php');
    expect($basePath.'/tests/Feature/Billing/IndexApiTest.php')->toBeFile();
    $this->assertFileDoesNotExist($basePath.'/tests/Feature/Billing/IndexPageTest.php');
    $this->assertFileDoesNotExist($basePath.'/resources/js/modules/billing/pages/Index.vue');
    expect($basePath.'/app/Models/Billing.php')->toBeFile();
    $this->assertMigrationFileExists($basePath, 'billings');

    $apiRouteFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/api.php');
    $apiRouteFileContents = is_string($apiRouteFileContents) ? $apiRouteFileContents : '';

    $this->assertStringContainsString("Route::prefix('v1/admin/billing')", $apiRouteFileContents);
    $this->assertStringContainsString("->as('api.v1.admin.billing.')", $apiRouteFileContents);
    $this->assertStringContainsString("'can:manage-billing'", $apiRouteFileContents);
    expect($basePath.'/app/Modules/Billing/Routes/gates.php')->toBeFile();

    $apiFeatureTestContents = file_get_contents($basePath.'/tests/Feature/Billing/IndexApiTest.php');
    $apiFeatureTestContents = is_string($apiFeatureTestContents) ? $apiFeatureTestContents : '';
    $this->assertStringContainsString('uses(RefreshDatabase::class);', $apiFeatureTestContents);
    $this->assertStringContainsString("getJson('/api/v1/admin/billing')", $apiFeatureTestContents);
    $this->assertStringContainsString("test('users without required role cannot list billing api results'", $apiFeatureTestContents);
    $this->assertStringContainsString("create(['role' => UserRole::Admin])", $apiFeatureTestContents);
    $this->assertStringNotContainsString('extends TestCase', $apiFeatureTestContents);

    $queryFileContents = file_get_contents($basePath.'/app/Modules/Billing/Queries/BillingQueries.php');
    $queryFileContents = is_string($queryFileContents) ? $queryFileContents : '';
    $this->assertStringContainsString('@return LengthAwarePaginator<int, Billing>', $queryFileContents);

    $apiControllerContents = file_get_contents($basePath.'/app/Modules/Billing/Http/Controllers/IndexApiController.php');
    $apiControllerContents = is_string($apiControllerContents) ? $apiControllerContents : '';
    $this->assertStringContainsString('use App\\Modules\\Shared\\Http\\Responders\\ApiResponder;', $apiControllerContents);
    $this->assertStringContainsString('return ApiResponder::collection(', $apiControllerContents);
    $this->assertStringContainsString('return ApiResponder::resource(IndexResource::make($billing), 201);', $apiControllerContents);
});
test('crud api mode scaffolds both backend route files', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--scaffold' => 'crud-api',
        '--route-profile' => 'app',
        '--roles' => 'all',
        '--api-route-profile' => 'protected',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(0);

    expect($basePath.'/app/Modules/Billing/Routes/web.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Routes/api.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Http/Controllers/IndexController.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Http/Controllers/IndexApiController.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Handlers/BillingQueryHandler.php')->toBeFile();
    expect($basePath.'/app/Modules/Billing/Handlers/BillingCommandHandler.php')->toBeFile();
    expect($basePath.'/tests/Feature/Billing/IndexPageTest.php')->toBeFile();
    expect($basePath.'/tests/Feature/Billing/IndexApiTest.php')->toBeFile();

    $webControllerContents = file_get_contents($basePath.'/app/Modules/Billing/Http/Controllers/IndexController.php');
    $webControllerContents = is_string($webControllerContents) ? $webControllerContents : '';
    $this->assertStringContainsString('use App\\Modules\\Billing\\Handlers\\BillingQueryHandler;', $webControllerContents);
    $this->assertStringContainsString('use App\\Modules\\Billing\\Handlers\\BillingCommandHandler;', $webControllerContents);
    $this->assertStringContainsString('private readonly BillingQueryHandler $billingQueryHandler', $webControllerContents);
    $this->assertStringContainsString('private readonly BillingCommandHandler $billingCommandHandler', $webControllerContents);

    $apiControllerContents = file_get_contents($basePath.'/app/Modules/Billing/Http/Controllers/IndexApiController.php');
    $apiControllerContents = is_string($apiControllerContents) ? $apiControllerContents : '';
    $this->assertStringContainsString('use App\\Modules\\Billing\\Handlers\\BillingQueryHandler;', $apiControllerContents);
    $this->assertStringContainsString('use App\\Modules\\Billing\\Handlers\\BillingCommandHandler;', $apiControllerContents);
    $this->assertStringContainsString('use App\\Modules\\Shared\\Http\\Responders\\ApiResponder;', $apiControllerContents);
    $this->assertStringContainsString('private readonly BillingQueryHandler $billingQueryHandler', $apiControllerContents);
    $this->assertStringContainsString('private readonly BillingCommandHandler $billingCommandHandler', $apiControllerContents);
    $this->assertStringContainsString('use App\\Modules\\Billing\\Data\\BillingIndexPageData;', $webControllerContents);
    $this->assertStringContainsString('PageResponder::render(', $webControllerContents);
});
test('extend mode reuses existing resource manifest defaults', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--scaffold' => 'crud',
        '--page' => 'Index',
        '--route-profile' => 'app',
        '--roles' => 'admin',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(0);

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--extend' => true,
        '--scaffold' => 'crud',
        '--page' => 'Index',
        '--no-interaction' => true,
        '--force' => true,
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(0);

    $routeFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/web.php');
    $routeFileContents = is_string($routeFileContents) ? $routeFileContents : '';

    $this->assertStringContainsString("Route::prefix('app/admin/billing')", $routeFileContents);
    $this->assertStringContainsString("'can:manage-billing'", $routeFileContents);

    $manifestContents = file_get_contents($basePath.'/app/Modules/Billing/Manifests/IndexResource.php');
    $manifestContents = is_string($manifestContents) ? $manifestContents : '';
    $this->assertStringContainsString("'roles' => ['admin']", $manifestContents);
    $this->assertMigrationCount($basePath, 'billings', 1);
});
test('extend mode generates missing model and migration for partial crud module', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();
    $this->ensureDirectory($basePath.'/app/Modules/Billing');
    $this->ensureDirectory($basePath.'/app/Modules/Billing/Manifests');

    file_put_contents(
        $basePath.'/app/Modules/Billing/Manifests/IndexResource.php',
        <<<'PHP'
<?php

declare(strict_types=1);

return [
    'page' => 'Index',
    'model' => 'Billing',
    'route' => [
        'profile' => 'app',
        'prefix' => 'app/admin/billing',
        'name_prefix' => 'app.admin.billing',
        'roles' => ['admin'],
        'middleware' => ['auth', 'verified', 'can:manage-billing'],
    ],
    'api' => [
        'enabled' => false,
        'route_profile' => 'protected',
        'route_prefix' => 'v1/admin/billing',
        'route_name_prefix' => 'api.v1.admin.billing',
        'middleware' => ['auth:sanctum'],
        'generates_resource' => false,
        'generates_feature_test' => false,
    ],
    'table' => [
        'columns' => [
            ['key' => 'name', 'label' => 'Name', 'type' => 'text', 'sortable' => true],
            ['key' => 'created_at', 'label' => 'Created', 'type' => 'date', 'sortable' => true],
        ],
        'mobile_fields' => [
            ['key' => 'name', 'label' => 'Name', 'type' => 'text'],
            ['key' => 'created_at', 'label' => 'Created', 'type' => 'date'],
        ],
    ],
    'form' => [
        'fields' => [
            ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
        ],
    ],
    'realtime' => [
        'enabled' => false,
    ],
];
PHP,
    );

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--extend' => true,
        '--scaffold' => 'crud',
        '--page' => 'Index',
        '--no-interaction' => true,
        '--force' => true,
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(0);

    expect($basePath.'/app/Models/Billing.php')->toBeFile();
    $this->assertMigrationFileExists($basePath, 'billings');
});
test('existing module requires extend flag', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();
    $this->ensureDirectory($basePath.'/app/Modules/Billing');

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--scaffold' => 'crud',
        '--page' => 'Index',
        '--route-profile' => 'app',
        '--roles' => 'all',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])
        ->expectsOutputToContain('Use --extend')
        ->assertExitCode(1);
});
test('extend mode with page scaffold creates only frontend page artifacts', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();
    $this->ensureDirectory($basePath.'/app/Modules/Billing');

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--extend' => true,
        '--scaffold' => 'page',
        '--page' => 'InviteUser',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(0);

    expect($basePath.'/resources/js/modules/billing/forms/invite-user-form-schema.ts')->toBeFile();
    expect($basePath.'/resources/js/modules/billing/pages/InviteUser.vue')->toBeFile();
    expect($basePath.'/resources/js/modules/billing/pages/__tests__/InviteUser.test.ts')->toBeFile();

    $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Http/Controllers/InviteUserController.php');
    $this->assertFileDoesNotExist($basePath.'/tests/Feature/Billing/InviteUserPageTest.php');
    $this->assertFileDoesNotExist($basePath.'/app/Models/Billing.php');
    $this->assertNoMigrationFileExists($basePath, 'billings');
});
test('dry run outputs plan without writing files', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--scaffold' => 'crud',
        '--page' => 'Index',
        '--route-profile' => 'app',
        '--roles' => 'all',
        '--dry-run' => true,
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])
        ->expectsOutputToContain('Dry run mode enabled')
        ->assertExitCode(0);

    $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Http/Controllers/IndexController.php');
    $this->assertFileDoesNotExist($basePath.'/app/Models/Billing.php');
    $this->assertFileDoesNotExist($basePath.'/resources/js/modules/billing/pages/Index.vue');
    $this->assertNoMigrationFileExists($basePath, 'billings');
});
test('existing target files require force to overwrite', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();
    $this->ensureDirectory($basePath.'/resources/js/modules/billing/forms');
    file_put_contents($basePath.'/resources/js/modules/billing/forms/index-form-schema.ts', 'existing');

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--scaffold' => 'crud',
        '--page' => 'Index',
        '--route-profile' => 'app',
        '--roles' => 'all',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])
        ->expectsOutputToContain('Use --force to overwrite')
        ->assertExitCode(1);
});
test('interactive file prompt allows selective generation for page scaffold', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();
    $this->ensureDirectory($basePath.'/app/Modules/Billing');

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--extend' => true,
        '--scaffold' => 'page',
        '--page' => 'InviteUser',
        '--base-path' => $basePath,
    ])
        ->expectsConfirmation('Generate file [resources/js/modules/billing/forms/invite-user-form-schema.ts]?', 'yes')
        ->expectsConfirmation('Generate file [resources/js/modules/billing/pages/InviteUser.vue]?', 'no')
        ->expectsConfirmation('Generate file [resources/js/modules/billing/pages/__tests__/InviteUser.test.ts]?', 'yes')
        ->assertExitCode(0);

    expect($basePath.'/resources/js/modules/billing/forms/invite-user-form-schema.ts')->toBeFile();
    $this->assertFileDoesNotExist($basePath.'/resources/js/modules/billing/pages/InviteUser.vue');
    expect($basePath.'/resources/js/modules/billing/pages/__tests__/InviteUser.test.ts')->toBeFile();
});
test('interactive prompt branches for api scaffold', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])
        ->expectsChoice(
            'Select scaffolding target for the generated module',
            'api',
            ['page', 'crud', 'api', 'crud-api'],
        )
        ->expectsChoice(
            'Select an API route profile for the generated module',
            'protected',
            ['protected', 'public', 'custom'],
        )
        ->expectsQuestion('Select allowed roles for protected generated routes (all, admin, user)', 'admin')
        ->assertExitCode(0);

    expect($basePath.'/app/Modules/Billing/Routes/api.php')->toBeFile();
    $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Routes/web.php');
});
test('non interactive app crud requires roles option', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--scaffold' => 'crud',
        '--route-profile' => 'app',
        '--no-interaction' => true,
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])
        ->expectsOutputToContain('The --roles option is required for app CRUD or protected API scaffolding')
        ->assertExitCode(1);
});
test('non interactive app crud api requires roles option', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--scaffold' => 'crud-api',
        '--route-profile' => 'app',
        '--api-route-profile' => 'protected',
        '--no-interaction' => true,
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])
        ->expectsOutputToContain('The --roles option is required for app CRUD or protected API scaffolding')
        ->assertExitCode(1);
});
test('admin only roles generate admin route shape and gate restriction', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--scaffold' => 'crud',
        '--route-profile' => 'app',
        '--roles' => 'admin',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(0);

    $routeFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/web.php');
    $routeFileContents = is_string($routeFileContents) ? $routeFileContents : '';

    $this->assertStringContainsString("Route::prefix('app/admin/billing')", $routeFileContents);
    $this->assertStringContainsString("->as('app.admin.billing.')", $routeFileContents);
    $this->assertStringContainsString("'can:manage-billing'", $routeFileContents);

    $gateFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/gates.php');
    $gateFileContents = is_string($gateFileContents) ? $gateFileContents : '';

    $this->assertStringContainsString("Gate::define('manage-billing'", $gateFileContents);
    $this->assertStringContainsString('UserRole::Admin', $gateFileContents);

    $dashboardNavContents = file_get_contents($basePath.'/resources/js/modules/billing/contracts/dashboard-nav.ts');
    $dashboardNavContents = is_string($dashboardNavContents) ? $dashboardNavContents : '';

    $this->assertStringContainsString('roles: [UserRole.Admin]', $dashboardNavContents);
});
test('multi role scope keeps default app route shape and generates role gate', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--scaffold' => 'crud',
        '--route-profile' => 'app',
        '--roles' => 'admin,user',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(0);

    $routeFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/web.php');
    $routeFileContents = is_string($routeFileContents) ? $routeFileContents : '';

    $this->assertStringContainsString("Route::prefix('app/billing')", $routeFileContents);
    $this->assertStringContainsString("->as('app.billing.')", $routeFileContents);
    $this->assertStringContainsString("'can:manage-billing'", $routeFileContents);

    $gateFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/gates.php');
    $gateFileContents = is_string($gateFileContents) ? $gateFileContents : '';

    $this->assertStringContainsString('UserRole::Admin', $gateFileContents);
    $this->assertStringContainsString('UserRole::User', $gateFileContents);

    $dashboardNavContents = file_get_contents($basePath.'/resources/js/modules/billing/contracts/dashboard-nav.ts');
    $dashboardNavContents = is_string($dashboardNavContents) ? $dashboardNavContents : '';

    $this->assertStringContainsString('roles: [UserRole.Admin, UserRole.User]', $dashboardNavContents);
});
test('all roles scope skips role gate file and role middleware', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--scaffold' => 'crud',
        '--route-profile' => 'app',
        '--roles' => 'all',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(0);

    $routeFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/web.php');
    $routeFileContents = is_string($routeFileContents) ? $routeFileContents : '';

    $this->assertStringNotContainsString('can:manage-billing', $routeFileContents);
    $this->assertFileDoesNotExist($basePath.'/app/Modules/Billing/Routes/gates.php');

    $dashboardNavContents = file_get_contents($basePath.'/resources/js/modules/billing/contracts/dashboard-nav.ts');
    $dashboardNavContents = is_string($dashboardNavContents) ? $dashboardNavContents : '';

    $this->assertStringContainsString("roles: 'all'", $dashboardNavContents);
});
test('interactive prompt asks for roles when scaffolding app crud', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();

    $this->runGenerateCommand([
        'module' => 'Billing',
        '--scaffold' => 'crud',
        '--route-profile' => 'app',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])
        ->expectsQuestion('Select allowed roles for protected generated routes (all, admin, user)', 'admin')
        ->assertExitCode(0);

    $routeFileContents = file_get_contents($basePath.'/app/Modules/Billing/Routes/web.php');
    $routeFileContents = is_string($routeFileContents) ? $routeFileContents : '';

    $this->assertStringContainsString("Route::prefix('app/admin/billing')", $routeFileContents);
    $this->assertStringContainsString("'can:manage-billing'", $routeFileContents);
});
test('multi word module names generate php safe feature test methods', function (): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();

    $this->runGenerateCommand([
        'module' => 'TempCheck',
        '--scaffold' => 'crud-api',
        '--route-profile' => 'app',
        '--roles' => 'admin',
        '--api-route-profile' => 'protected',
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(0);

    $pageTestContents = file_get_contents($basePath.'/tests/Feature/TempCheck/IndexPageTest.php');
    $pageTestContents = is_string($pageTestContents) ? $pageTestContents : '';
    $this->assertStringContainsString("test('guests access behavior for temp check page'", $pageTestContents);
    $this->assertStringContainsString("test('authenticated users can visit temp check page'", $pageTestContents);
    $this->assertStringNotContainsString('temp_check', $pageTestContents);

    $apiTestContents = file_get_contents($basePath.'/tests/Feature/TempCheck/IndexApiTest.php');
    $apiTestContents = is_string($apiTestContents) ? $apiTestContents : '';
    $this->assertStringContainsString("test('guests access behavior for temp check api index'", $apiTestContents);
    $this->assertStringContainsString("test('authenticated users can list temp check api results'", $apiTestContents);
    $this->assertStringNotContainsString('temp_check', $apiTestContents);

    $pageDataContents = file_get_contents($basePath.'/app/Modules/TempCheck/Data/TempCheckIndexPageData.php');
    $pageDataContents = is_string($pageDataContents) ? $pageDataContents : '';
    $this->assertStringContainsString('final class TempCheckIndexPageData extends Data', $pageDataContents);

    $listItemContents = file_get_contents($basePath.'/app/Modules/TempCheck/Data/TempCheckIndexListItemData.php');
    $listItemContents = is_string($listItemContents) ? $listItemContents : '';
    $this->assertStringContainsString('final class TempCheckIndexListItemData extends Data', $listItemContents);

    $crudContractContents = file_get_contents($basePath.'/resources/js/modules/temp-check/contracts/index-crud.ts');
    $crudContractContents = is_string($crudContractContents) ? $crudContractContents : '';
    $this->assertStringContainsString("import type { TempCheckIndexListItemData } from '@/types/app-data'", $crudContractContents);

    $pageContents = file_get_contents($basePath.'/resources/js/modules/temp-check/pages/Index.vue');
    $pageContents = is_string($pageContents) ? $pageContents : '';
    $this->assertStringContainsString("import type { TempCheckIndexPageData } from '@/types/app-data'", $pageContents);

    $dashboardNavContents = file_get_contents($basePath.'/resources/js/modules/temp-check/contracts/dashboard-nav.ts');
    $dashboardNavContents = is_string($dashboardNavContents) ? $dashboardNavContents : '';
    $this->assertStringContainsString('href: appRoutes.admin.tempCheck.index.url()', $dashboardNavContents);
});
