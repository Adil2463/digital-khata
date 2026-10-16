<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\PasswordUpdateRequest;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Shared\Http\Responders\PageResponder;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

final class PasswordController extends Controller
{
    /**
     * Show the user's password settings page.
     */
    public function edit(): Response
    {
        return PageResponder::render('modules/settings/pages/Password');
    }

    /**
     * Update the user's password.
     */
    public function update(PasswordUpdateRequest $passwordUpdateRequest): RedirectResponse
    {
        $passwordUpdateData = $passwordUpdateRequest->toDto();
        $user = RequestActor::from($passwordUpdateRequest);

        $user->update([
            'password' => $passwordUpdateData->password,
        ]);

        return back();
    }
}
