<?php

declare(strict_types=1);

use App\Enums\ShopTimezone;
use App\Models\Shop;
use App\Models\User;
use App\Modules\Shared\Shop\CurrentShop;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('the current shop is the one the account belongs to', function (): void {
    Shop::factory()->create(['name' => 'First Shop']);
    $secondShop = Shop::factory()->create(['name' => 'Second Shop']);
    $user = User::factory()->create(['shop_id' => $secondShop->id]);

    expect(CurrentShop::of($user)->is($secondShop))->toBeTrue();
});

test('an account without a shop has no shop context', function (): void {
    expect(fn (): Shop => CurrentShop::of(User::factory()->withoutShop()->create()))
        ->toThrow(AuthorizationException::class, 'This account does not belong to a shop.');
});

test('a shop row with an unsupported timezone fails instead of dating entries wrongly', function (): void {
    $shop = Shop::factory()->create();
    DB::table('shops')->where('id', $shop->id)->update(['timezone' => 'Asia/Dubai']);

    expect(fn (): ShopTimezone => Shop::query()->findOrFail($shop->id)->timezone)->toThrow(ValueError::class);
});
