<?php

declare(strict_types=1);

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Settings\Data\ProfilePageData;
use App\Modules\Settings\Http\Requests\ProfileDestroyRequest;
use App\Modules\Settings\Http\Requests\ProfileUpdateRequest;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Shared\Http\Responders\PageResponder;
use App\Modules\Shared\Support\SessionHelper;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class ProfileController extends Controller
{
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
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $profileUpdateRequest): RedirectResponse
    {
        $user = RequestActor::from($profileUpdateRequest);
        $profileUpdateData = $profileUpdateRequest->toDto();

        $user->fill([
            'name' => $profileUpdateData->name,
            'email' => $profileUpdateData->email,
        ]);

        $user->save();

        return to_route('app.settings.profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDestroyRequest $profileDestroyRequest): RedirectResponse
    {
        $user = RequestActor::from($profileDestroyRequest);

        Auth::logout();

        $user->delete();

        $profileDestroyRequest->session()->invalidate();
        $profileDestroyRequest->session()->regenerateToken();
        Inertia::clearHistory();

        return redirect('/');
    }
}
