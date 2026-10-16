<?php

declare(strict_types=1);

use App\Modules\Settings\Http\Controllers\PasswordController;
use App\Modules\Settings\Http\Controllers\ProfileController;
use App\Modules\Shared\Http\Responders\PageResponder;
use Illuminate\Support\Facades\Route;
use Inertia\Response;

Route::middleware('auth')
    ->prefix('app/settings')
    ->as('app.settings.')
    ->group(function (): void {
        Route::redirect('/', '/app/settings/profile');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('password', [PasswordController::class, 'edit'])->name('password.edit');
        Route::put('password', [PasswordController::class, 'update'])->name('password.update');

        Route::get('appearance', fn (): Response => PageResponder::render('modules/settings/pages/Appearance'))->name('appearance');
    });
