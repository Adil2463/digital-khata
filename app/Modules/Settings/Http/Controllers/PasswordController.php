<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Http\Requests\PasswordUpdateRequest;
use App\Modules\Shared\Auth\CredentialRotator;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Shared\Http\Responders\PageResponder;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

final class PasswordController extends Controller
{
    public function __construct(
        private readonly CredentialRotator $credentialRotator,
    ) {}

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

        $this->credentialRotator->replacePassword(
            $user,
            $passwordUpdateData->password,
            $passwordUpdateRequest->session()->getId(),
        );

        return back();
    }
}
