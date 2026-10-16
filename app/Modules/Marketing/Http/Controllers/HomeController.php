<?php

declare(strict_types=1);

namespace App\Modules\Marketing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Digital Khata is a private shop app, so the root has no public page of its own.
 * `EnsureAccountCanSignIn` runs first, so an account that may not sign in never
 * reaches the dashboard redirect.
 */
final class HomeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return to_route($request->user() === null ? 'auth.login.create' : 'app.dashboard');
    }
}
