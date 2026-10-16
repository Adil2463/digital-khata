<?php

declare(strict_types=1);
use App\Modules\Shared\Providers\ModuleServiceProvider;
use App\Modules\Shared\Realtime\Contracts\RealtimeDispatcher;
use App\Modules\Shared\Realtime\Support\LaravelRealtimeDispatcher;

test('shared module service provider is auto registered', function (): void {
    expect(app()->providerIsLoaded(ModuleServiceProvider::class))->toBeTrue();
    expect(get_debug_type(app(RealtimeDispatcher::class)))->toBe(LaravelRealtimeDispatcher::class);
});
