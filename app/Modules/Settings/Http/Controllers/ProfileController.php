<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Data\ProfilePageData;
use App\Modules\Settings\Http\Requests\ProfileUpdateRequest;
use App\Modules\Shared\Auth\CredentialRotator;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Shared\Http\Responders\PageResponder;
use App\Modules\Shared\Support\SessionHelper;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

final class ProfileController extends Controller
{
    public function __construct(
        private readonly CredentialRotator $credentialRotator,
    ) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = RequestActor::from($request);

        return PageResponder::render(
            'modules/settings/pages/Profile',
            new ProfilePageData(
                mustVerifyEmail: in_array(MustVerifyEmail::class, class_implements($user), true),
                status: SessionHelper::resolveStatus($request),
            ),
        );
    }

    /**
     * A changed login signs the account out on other devices; this session stays.
     */
    public function update(ProfileUpdateRequest $profileUpdateRequest): RedirectResponse
    {
        $user = RequestActor::from($profileUpdateRequest);
        $profileUpdateData = $profileUpdateRequest->toDto();

        $user->fill([
            'name' => $profileUpdateData->name,
            'email' => $profileUpdateData->email,
        ]);

        $loginChanged = $user->isDirty('email');

        $user->save();

        if ($loginChanged) {
            $this->credentialRotator->revokeAccess($user, $profileUpdateRequest->session()->getId());
        }

        return to_route('app.settings.profile.edit');
    }
}
