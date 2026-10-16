<?php

declare(strict_types=1);

use App\Modules\Api\V1\Http\Controllers\AdminUserController;
use App\Modules\Api\V1\Http\Controllers\MeController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->as('api.v1.')->group(function (): void {
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::get('me', MeController::class)->name('me.show');
    });

    Route::middleware(['auth:sanctum', 'can:manage-users'])
        ->prefix('admin')
        ->as('admin.')
        ->group(function (): void {
            Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
            Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
            Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        });
});
