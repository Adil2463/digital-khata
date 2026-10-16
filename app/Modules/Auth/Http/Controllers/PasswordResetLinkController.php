<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Data\ForgotPasswordPageData;
use App\Modules\Auth\Http\Requests\PasswordResetLinkRequest;
use App\Modules\Shared\Http\Responders\PageResponder;
use App\Modules\Shared\Support\SessionHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Response;

final class PasswordResetLinkController extends Controller
{
    /**
     * Show the password reset link request page.
     */
    public function create(Request $request): Response
    {
        return PageResponder::render(
            'modules/auth/pages/ForgotPassword',
            new ForgotPasswordPageData(
                status: SessionHelper::resolveStatus($request),
            ),
        );
    }

    public function store(PasswordResetLinkRequest $passwordResetLinkRequest): RedirectResponse
    {
        $passwordResetLinkData = $passwordResetLinkRequest->toDto();

        Password::sendResetLink([
            'email' => $passwordResetLinkData->email,
        ]);

        return back()->with('status', __('A reset link will be sent if the account exists.'));
    }
}
