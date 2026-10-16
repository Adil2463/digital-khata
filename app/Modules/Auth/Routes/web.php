<?php

declare(strict_types=1);

use App\Modules\Auth\Http\Controllers\AuthenticatedSessionController;
use App\Modules\Auth\Http\Controllers\ConfirmablePasswordController;
use App\Modules\Auth\Http\Controllers\EmailVerificationNotificationController;
use App\Modules\Auth\Http\Controllers\EmailVerificationPromptController;
use App\Modules\Auth\Http\Controllers\NewPasswordController;
use App\Modules\Auth\Http\Controllers\PasswordResetLinkController;
use App\Modules\Auth\Http\Controllers\RegisteredUserController;
use App\Modules\Auth\Http\Controllers\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->as('auth.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('register', [RegisteredUserController::class, 'create'])->name('register.create');
        Route::post('register', [RegisteredUserController::class, 'store'])
            ->middleware('throttle:auth-sensitive')
            ->name('register.store');

        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login.create');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
            ->middleware('throttle:auth-sensitive')
            ->name('password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])
            ->middleware('throttle:auth-sensitive')
            ->name('password.store');
    });

    Route::middleware('auth')->group(function (): void {
        Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
        Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:auth-sensitive')
            ->name('verification.send');

        Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
        Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])
            ->middleware('throttle:auth-sensitive')
            ->name('password.confirm.store');

        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});
