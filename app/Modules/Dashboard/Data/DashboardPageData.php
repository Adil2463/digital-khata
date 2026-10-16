<?php

declare(strict_types=1);

namespace App\Modules\Dashboard\Data;

use App\Models\Shop;
use App\Modules\Shared\Shop\ShopClock;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * The server clock supplies "now" so the server render and hydration show the same day.
 */
#[TypeScript]
final class DashboardPageData extends Data
{
    public function __construct(
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public CarbonImmutable $shopTime,
    ) {}

    public static function fromShop(Shop $shop): self
    {
        return new self(shopTime: ShopClock::now($shop));
    }
}
