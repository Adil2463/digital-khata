<?php

declare(strict_types=1);

namespace App\Modules\Shop\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Auth\RequestActor;
use App\Modules\Shared\Http\Responders\PageResponder;
use App\Modules\Shared\Shop\CurrentShop;
use App\Modules\Shop\Commands\ShopCommands;
use App\Modules\Shop\Data\ShopSettingsPageData;
use App\Modules\Shop\Http\Requests\ShopSettingsUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

final class ShopSettingsController extends Controller
{
    public function __construct(
        private readonly ShopCommands $shopCommands,
    ) {}

    public function edit(Request $request): Response
    {
        return PageResponder::render(
            'modules/shop/pages/Settings',
            ShopSettingsPageData::fromShop(CurrentShop::of(RequestActor::from($request))),
        );
    }

    public function update(ShopSettingsUpdateRequest $shopSettingsUpdateRequest): RedirectResponse
    {
        $user = RequestActor::from($shopSettingsUpdateRequest);

        $this->shopCommands->updateSettings(
            CurrentShop::of($user),
            $shopSettingsUpdateRequest->toDto(),
            $user,
        );

        return to_route('app.shop.settings.edit')->with('message', 'Shop settings saved.');
    }
}
