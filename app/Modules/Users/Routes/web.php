<?php

declare(strict_types=1);

use App\Modules\Users\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-users'])
    ->prefix('app/admin')
    ->as('app.admin.')
    ->group(function (): void {
        Route::resource('users', UserController::class)->only(['index', 'store', 'update']);
        Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
        Route::patch('users/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');
    });
