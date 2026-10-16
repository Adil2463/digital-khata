<?php

declare(strict_types=1);

use App\Modules\Users\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-users'])
    ->prefix('app/admin')
    ->as('app.admin.')
    ->group(function (): void {
        Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
    });
