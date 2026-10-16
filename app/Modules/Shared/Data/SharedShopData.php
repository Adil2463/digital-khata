<?php

declare(strict_types=1);

namespace App\Modules\Shared\Data;

use App\Enums\ShopTimezone;
use App\Models\Shop;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class SharedShopData extends Data
{
    public function __construct(
        public string $name,
        public ShopTimezone $timezone,
    ) {}

    public static function fromShop(Shop $shop): self
    {
        return new self(
            name: $shop->name,
            timezone: $shop->timezone,
        );
    }
}
