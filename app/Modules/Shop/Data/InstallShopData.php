<?php

declare(strict_types=1);

namespace App\Modules\Shop\Data;

use App\Enums\ShopTimezone;
use Spatie\LaravelData\Data;

final class InstallShopData extends Data
{
    public function __construct(
        public string $shopName,
        public ShopTimezone $timezone,
        public string $ownerName,
        public string $ownerEmail,
        public string $ownerPassword,
    ) {}
}
