<?php

declare(strict_types=1);

namespace App\Modules\Shop\Data;

use App\Models\Shop;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class ShopSettingsUpdateData extends Data
{
    public function __construct(
        public string $name,
        public ?string $phone,
        public ?string $address,
        public ?string $receipt_footer,
    ) {}

    public static function fromShop(Shop $shop): self
    {
        return new self(
            name: $shop->name,
            phone: $shop->phone,
            address: $shop->address,
            receipt_footer: $shop->receipt_footer,
        );
    }
}
