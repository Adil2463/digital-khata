<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Auth\RequestActor;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

final class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $emailVerificationRequest): RedirectResponse
    {
        $user = RequestActor::from($emailVerificationRequest);

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('app.dashboard', absolute: false).'?verified=1');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()->intended(route('app.dashboard', absolute: false).'?verified=1');
    }
}
