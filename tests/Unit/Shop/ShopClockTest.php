<?php

declare(strict_types=1);

use App\Enums\ShopTimezone;
use App\Models\Shop;
use App\Modules\Shared\Shop\ShopClock;
use Carbon\CarbonImmutable;

function karachiShop(): Shop
{
    return new Shop(['name' => 'Clock Shop', 'timezone' => ShopTimezone::AsiaKarachi]);
}

test('the business day changes at midnight Pakistan time, which is 19:00 UTC', function (string $utcInstant, string $expectedDay): void {
    $this->travelTo(CarbonImmutable::parse($utcInstant, 'UTC'));

    $today = ShopClock::today(karachiShop());

    expect($today->toDateString())->toBe($expectedDay)
        ->and($today->format('H:i:s P'))->toBe('00:00:00 +05:00');
})->with([
    'last second of 16 Oct PKT' => ['2026-10-16 18:59:59', '2026-10-16'],
    'first second of 17 Oct PKT' => ['2026-10-16 19:00:00', '2026-10-17'],
    '00:30 PKT belongs to the new day' => ['2026-10-16 19:30:00', '2026-10-17'],
    'UTC midnight is already 5 AM in Pakistan' => ['2026-10-17 00:00:00', '2026-10-17'],
    'new year arrives five hours before UTC' => ['2026-12-31 19:00:00', '2027-01-01'],
]);

test('local time keeps the instant and expresses it in the shop timezone', function (): void {
    $stored = CarbonImmutable::parse('2026-10-16 19:30:00', 'UTC');

    $local = ShopClock::local($stored, karachiShop());

    expect($local->format('Y-m-d H:i P'))->toBe('2026-10-17 00:30 +05:00')
        ->and($local->equalTo($stored))->toBeTrue()
        ->and($stored->getTimezone()->getName())->toBe('UTC');
});

test('neither the server nor the configured app timezone changes the business day', function (): void {
    $originalTimezone = date_default_timezone_get();
    $this->travelTo(CarbonImmutable::parse('2026-10-16 19:00:00', 'UTC'));

    try {
        date_default_timezone_set('America/New_York');
        config()->set('app.timezone', 'America/New_York');

        expect(ShopClock::today(karachiShop())->toDateString())->toBe('2026-10-17')
            ->and(ShopClock::now(karachiShop())->format('Y-m-d H:i P'))->toBe('2026-10-17 00:00 +05:00')
            ->and(ShopClock::local(CarbonImmutable::parse('2026-10-16T18:59:59Z'), karachiShop())->toDateString())->toBe('2026-10-16');
    } finally {
        date_default_timezone_set($originalTimezone);
    }
});

test('only supported timezones can be assigned to a shop', function (): void {
    expect(ShopTimezone::cases())->toBe([ShopTimezone::AsiaKarachi])
        ->and(ShopTimezone::DEFAULT)->toBe(ShopTimezone::AsiaKarachi)
        ->and(fn (): Shop => new Shop(['timezone' => 'Asia/Dubai']))->toThrow(ValueError::class);
});
