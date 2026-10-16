<?php

declare(strict_types=1);

use App\Modules\Shared\Support\ModuleRegistry;
use Illuminate\Broadcasting\BroadcastController;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->withoutMiddleware([PreventRequestForgery::class])
    ->match(['get', 'post'], 'broadcasting/auth', [BroadcastController::class, 'authenticate']);

Route::middleware('auth:sanctum')
    ->withoutMiddleware([PreventRequestForgery::class])
    ->match(['get', 'post'], 'broadcasting/user-auth', [BroadcastController::class, 'authenticateUser']);

$moduleRouteFiles = ModuleRegistry::apiRoutes(base_path());

foreach ($moduleRouteFiles as $moduleRouteFile) {
    require $moduleRouteFile;
}
