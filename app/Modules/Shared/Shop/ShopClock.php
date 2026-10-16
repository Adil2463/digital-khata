<?php

declare(strict_types=1);

namespace App\Modules\Shared\Shop;

use App\Models\Shop;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Timestamps are stored in UTC; business days follow the shop's timezone. Every
 * conversion names that timezone explicitly, so neither the server nor the PHP default
 * timezone can shift which day an instant belongs to.
 */
final class ShopClock
{
    public static function now(Shop $shop): CarbonImmutable
    {
        return CarbonImmutable::now($shop->timezone->value);
    }

    /**
     * The start of the current business day in the shop timezone.
     */
    public static function today(Shop $shop): CarbonImmutable
    {
        return self::now($shop)->startOfDay();
    }

    /**
     * The same instant expressed in the shop timezone; its date is the business date.
     */
    public static function local(CarbonInterface $instant, Shop $shop): CarbonImmutable
    {
        return CarbonImmutable::instance($instant)->setTimezone($shop->timezone->value);
    }
}
