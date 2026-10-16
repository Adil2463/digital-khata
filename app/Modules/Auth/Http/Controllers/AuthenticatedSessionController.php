<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Data\LoginPageData;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Shared\Http\Responders\PageResponder;
use App\Modules\Shared\Support\SessionHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

final class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return PageResponder::render(
            'modules/auth/pages/Login',
            new LoginPageData(
                canResetPassword: Route::has('auth.password.request'),
                status: SessionHelper::resolveStatus($request),
            ),
        );
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $loginRequest): RedirectResponse
    {
        $loginRequest->authenticate();

        $loginRequest->session()->regenerate();
        Inertia::clearHistory();

        return redirect()->intended(route('app.dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        Inertia::clearHistory();

        return redirect('/');
    }
}
