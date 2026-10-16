<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('generated api variants validate pagination and serialize later pages', function (string $scaffold, bool $resource): void {
    $basePath = $this->createTemporaryModuleGenerationBasePath();
    $module = 'Audit'.($scaffold === 'api' ? 'Standalone' : 'Combined').($resource ? 'Resource' : 'Plain');

    $this->runGenerateCommand([
        'module' => $module,
        '--scaffold' => $scaffold,
        '--route-profile' => 'public',
        '--api-route-profile' => 'public',
        '--no-api-resource' => ! $resource,
        '--no-file-prompts' => true,
        '--base-path' => $basePath,
    ])->assertExitCode(0);

    $loader = static function (string $class) use ($basePath, $module): void {
        if (! str_starts_with($class, 'App\\Modules\\'.$module.'\\') && $class !== 'App\\Models\\'.$module) {
            return;
        }

        $file = $basePath.'/app/'.str_replace('\\', '/', mb_substr($class, 4)).'.php';
        if (is_file($file)) {
            require_once $file;
        }
    };
    spl_autoload_register($loader);

    try {
        $table = Str::snake(Str::pluralStudly($module));
        Schema::create($table, static function (Blueprint $blueprint): void {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->timestamps();
        });
        foreach (range(1, 16) as $index) {
            DB::table($table)->insert(['name' => 'Record '.$index, 'created_at' => now(), 'updated_at' => now()]);
        }

        Route::get('/_audit/generated', 'App\\Modules\\'.$module.'\\Http\\Controllers\\IndexApiController@index');
        $this->getJson('/_audit/generated?page=2&perPage=15')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Record 1')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.total', 16);
        $this->getJson('/_audit/generated?page=0&perPage=101')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['page', 'perPage']);
        $this->getJson('/_audit/generated?page=&perPage=')
            ->assertOk()
            ->assertJsonCount(15, 'data');

        if ($resource) {
            $nextPage = $this->getJson('/_audit/generated?perPage=5')->assertOk()->json('links.next');
            expect($nextPage)->toBeString();
            if (! is_string($nextPage)) {
                $this->fail('Expected a next-page URL.');
            }

            $this->getJson($nextPage)
                ->assertOk()
                ->assertJsonCount(5, 'data')
                ->assertJsonPath('meta.current_page', 2)
                ->assertJsonPath('meta.per_page', 5)
                ->assertJsonPath('data.0.name', 'Record 11');
        }

        $modelClass = 'App\\Models\\'.$module;
        if (! is_subclass_of($modelClass, Model::class)) {
            $this->fail('Expected the generated Eloquent model.');
        }

        $modelClass::saving(static fn (): bool => false);
        $modelClass::deleting(static fn (): bool => false);
        $controller = 'App\\Modules\\'.$module.'\\Http\\Controllers\\IndexApiController';
        $parameter = lcfirst($module);
        Route::post('/_audit/generated', $controller.'@store');
        Route::put('/_audit/generated/{'.$parameter.'}', $controller.'@update');
        Route::delete('/_audit/generated/{'.$parameter.'}', $controller.'@destroy');
        $this->postJson('/_audit/generated', ['name' => 'Cancelled'])->assertStatus(500);
        $this->putJson('/_audit/generated/1', ['name' => 'Cancelled'])->assertStatus(500);
        $this->deleteJson('/_audit/generated/1')->assertStatus(500);
        $this->assertDatabaseCount($table, 16);
        $this->assertDatabaseHas($table, ['id' => 1, 'name' => 'Record 1']);

        if ($scaffold === 'crud-api') {
            Route::get('/_audit/page', 'App\\Modules\\'.$module.'\\Http\\Controllers\\IndexController@index');
            $this->withHeader('X-Inertia', 'true')->get('/_audit/page?page=2&perPage=15')
                ->assertOk()
                ->assertJsonPath('props.pagination.current_page', 2)
                ->assertJsonPath('props.pagination.total', 16)
                ->assertJsonCount(1, 'props.items');
        }
    } finally {
        spl_autoload_unregister($loader);
    }
})->with([
    ['api', false],
    ['api', true],
    ['crud-api', false],
    ['crud-api', true],
]);
