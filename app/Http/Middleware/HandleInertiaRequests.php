<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\Appearance;
use App\Modules\Shared\Data\SharedAuthData;
use App\Modules\Shared\Data\SharedFlashData;
use App\Modules\Shared\Data\SharedPageData;
use App\Modules\Shared\Data\SharedQuoteData;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Override;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    #[Override]
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function share(Request $request): array
    {
        $quote = Inspiring::quotes()->random();
        $quoteText = is_string($quote) ? $quote : '';
        $quoteParts = explode('-', $quoteText, 2);
        $message = $quoteParts[0];
        $author = $quoteParts[1] ?? '';

        return [
            ...parent::share($request),
            ...new SharedPageData(
                name: config()->string('app.name'),
                quote: new SharedQuoteData(mb_trim($message), mb_trim($author)),
                auth: new SharedAuthData($request->user()?->toViewData()),
                flash: SharedFlashData::fromSession($request->session()),
                location: $request->fullUrl(),
                sidebarOpen: ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
                appearance: Appearance::fromCookie($request->cookie(Appearance::COOKIE)),
            )->toArray(),
        ];
    }
}
