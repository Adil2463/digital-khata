<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\ConfirmPasswordRequest;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Shared\Http\Responders\PageResponder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Response;

final class ConfirmablePasswordController extends Controller
{
    /**
     * Show the confirm password page.
     */
    public function show(): Response
    {
        return PageResponder::render('modules/auth/pages/ConfirmPassword');
    }

    /**
     * Confirm the user's password.
     */
    public function store(ConfirmPasswordRequest $confirmPasswordRequest): RedirectResponse
    {
        $user = RequestActor::from($confirmPasswordRequest);
        $confirmPasswordData = $confirmPasswordRequest->toDto();

        if (! Auth::guard('web')->validate([
            'email' => $user->email,
            'password' => $confirmPasswordData->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $confirmPasswordRequest->session()->put('auth.password_confirmed_at', time());

        return redirect()->intended(route('app.dashboard', absolute: false));
    }
}
