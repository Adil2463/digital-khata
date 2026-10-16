<?php

declare(strict_types=1);

namespace App\Modules\Shop\Data;

use App\Enums\ShopTimezone;
use App\Models\Shop;
use App\Modules\Shared\Shop\ShopClock;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\Attributes\WithTransformer;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Transformers\DateTimeInterfaceTransformer;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * Instants are sent as UTC ISO-8601 so the browser formats them in the shop timezone;
 * the server clock supplies "now" so the server render and hydration show the same time.
 */
#[TypeScript]
final class ShopSettingsPageData extends Data
{
    public function __construct(
        public ShopSettingsUpdateData $settings,
        public ShopTimezone $timezone,
        public string $timezoneLabel,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public CarbonImmutable $shopTime,
        #[WithTransformer(DateTimeInterfaceTransformer::class, format: DATE_ATOM)]
        public ?CarbonImmutable $updatedAt,
    ) {}

    public static function fromShop(Shop $shop): self
    {
        $updatedAt = $shop->updated_at;

        return new self(
            settings: ShopSettingsUpdateData::fromShop($shop),
            timezone: $shop->timezone,
            timezoneLabel: $shop->timezone->label(),
            shopTime: ShopClock::now($shop),
            updatedAt: $updatedAt === null ? null : CarbonImmutable::instance($updatedAt),
        );
    }
}
