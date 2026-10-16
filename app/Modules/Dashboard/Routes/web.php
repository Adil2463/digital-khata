<?php

declare(strict_types=1);

use App\Modules\Dashboard\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('app')
    ->as('app.')
    ->group(function (): void {
        Route::get('dashboard', DashboardController::class)->name('dashboard');
    });
