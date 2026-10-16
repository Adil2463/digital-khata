<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The business timezones a shop may use. V1 serves shops in Pakistan only; adding a case
 * here is the single change that makes another timezone valid everywhere.
 */
enum ShopTimezone: string
{
    case AsiaKarachi = 'Asia/Karachi';

    public const self DEFAULT = self::AsiaKarachi;

    public function label(): string
    {
        return match ($this) {
            self::AsiaKarachi => 'Pakistan Standard Time (PKT, UTC+05:00)',
        };
    }
}
