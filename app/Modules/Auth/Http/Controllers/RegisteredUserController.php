<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Http\Requests\RegisterUserRequest;
use App\Modules\Shared\Http\Responders\PageResponder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

final class RegisteredUserController extends Controller
{
    /**
     * Show the registration page.
     */
    public function create(): Response
    {
        return PageResponder::render('modules/auth/pages/Register');
    }

    public function store(RegisterUserRequest $registerUserRequest): RedirectResponse
    {
        $registerUserData = $registerUserRequest->toDto();

        $user = User::create([
            'name' => $registerUserData->name,
            'email' => $registerUserData->email,
            'password' => Hash::make($registerUserData->password),
        ]);

        event(new Registered($user));

        Auth::login($user);
        Inertia::clearHistory();

        return to_route('app.dashboard');
    }
}
