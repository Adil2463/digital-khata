<?php

declare(strict_types=1);

use App\Modules\Shop\Http\Controllers\ShopSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'can:manage-shop'])
    ->prefix('app/shop')
    ->as('app.shop.')
    ->group(function (): void {
        Route::get('settings', [ShopSettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [ShopSettingsController::class, 'update'])->name('settings.update');
    });
