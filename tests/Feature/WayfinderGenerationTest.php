<?php

declare(strict_types=1);

use App\Modules\Users\Http\Controllers\UserController;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

test('wayfinder writes portable actions routes and form helpers', function (): void {
    $path = $this->temporaryDirectoryPath('wayfinder');

    expect(Artisan::call('wayfinder:generate', ['--path' => $path, '--with-form' => true]))->toBe(0);

    $filesystem = app(Filesystem::class);

    foreach ($filesystem->allFiles($path) as $file) {
        expect($file->getContents())->not->toContain("\r");
    }

    $actions = $filesystem->get($path.'/actions/App/Modules/Users/Http/Controllers/UserController.ts');
    $routes = $filesystem->get($path.'/routes/app/admin/users/index.ts');

    foreach ([$actions, $routes] as $source) {
        expect($source)
            ->toContain("url: '/app/admin/users/{user}'")
            ->toContain('update.form')
            ->toContain("\n    if (Array.isArray(args)) {\n        args = {\n            user: args[0],\n        }\n    }");
    }

    expect($filesystem->get($path.'/wayfinder/index.ts'))->toContain('export type RouteFormDefinition');
    expect($filesystem->get($path.'/routes/app/index.ts'))->toContain("\n    settings: Object.assign(settings, settings),");
});

test('wayfinder supports query routes in action route and form helpers', function (): void {
    Route::query('/query-users', [UserController::class, 'index'])
        ->name('query.users');

    $path = $this->temporaryDirectoryPath('wayfinder-query');

    expect(Artisan::call('wayfinder:generate', ['--path' => $path, '--with-form' => true]))->toBe(0);

    $filesystem = app(Filesystem::class);

    foreach ([
        $path.'/actions/App/Modules/Users/Http/Controllers/UserController.ts',
        $path.'/routes/query/index.ts',
    ] as $file) {
        expect($filesystem->get($file))
            ->toContain("RouteDefinition<'query'>")
            ->toContain("method: 'query'")
            ->toContain("_method: 'QUERY'");
    }

    expect($filesystem->get($path.'/wayfinder/index.ts'))->toContain('| "query"');
});
