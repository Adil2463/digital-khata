<?php

declare(strict_types=1);

use App\Enums\ShopTimezone;
use App\Models\Shop;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function shopSettingsPayload(array $overrides = []): array
{
    return [
        'name' => 'Bismillah Karyana Store',
        'phone' => '0300-1234567',
        'address' => 'Shop 4, Main Bazaar, Gulberg, Lahore',
        'receipt_footer' => 'Shukriya! Phir tashreef laiye.',
        ...$overrides,
    ];
}

function shopWithOwner(string $name = 'Original Store'): User
{
    $shop = Shop::factory()->create(['name' => $name, 'phone' => '042-35761234', 'address' => 'Old address', 'receipt_footer' => null]);

    return User::factory()->owner()->create(['shop_id' => $shop->id]);
}

test('guests are sent to login from shop settings', function (string $method): void {
    $this->call($method, '/app/shop/settings', shopSettingsPayload())->assertRedirect('/auth/login');
})->with(['GET', 'PUT']);

test('owners see their own shop settings in shop time', function (): void {
    $this->travelTo(CarbonImmutable::parse('2026-10-16 19:30:00', 'UTC'));
    $user = shopWithOwner();
    $otherShop = Shop::factory()->create(['name' => 'Someone Else']);
    User::factory()->owner()->create(['shop_id' => $otherShop->id]);

    $this->actingAs($user)
        ->get('/app/shop/settings')
        ->assertOk()
        ->assertInertia(fn (Assert $assert): Assert => $assert
            ->component('modules/shop/pages/Settings')
            ->where('settings', [
                'name' => 'Original Store',
                'phone' => '042-35761234',
                'address' => 'Old address',
                'receipt_footer' => null,
            ])
            ->where('timezone', ShopTimezone::AsiaKarachi->value)
            ->where('timezoneLabel', 'Pakistan Standard Time (PKT, UTC+05:00)')
            ->where('shopTime', '2026-10-16T19:30:00+00:00')
            ->where('updatedAt', '2026-10-16T19:30:00+00:00')
            ->where('shop.name', 'Original Store')
            ->missing('settings.id')
            ->missing('settings.shop_id'));
});

test('owners update their shop details with normalized text', function (): void {
    $user = shopWithOwner();
    $shopId = $user->shop_id;

    $this->actingAs($user)
        ->from('/app/shop/settings')
        ->put('/app/shop/settings', shopSettingsPayload([
            'name' => '  Bismillah   Karyana  Store ',
            'phone' => ' +92 300 1234567 ',
            'address' => "Shop 4,\n  Main Bazaar ",
        ]))
        ->assertSessionHasNoErrors()
        ->assertRedirect('/app/shop/settings')
        ->assertSessionHas('message', 'Shop settings saved.');

    expect(Shop::query()->findOrFail($shopId)->only(['name', 'phone', 'address', 'receipt_footer', 'timezone']))->toBe([
        'name' => 'Bismillah Karyana Store',
        'phone' => '+92 300 1234567',
        'address' => 'Shop 4, Main Bazaar',
        'receipt_footer' => 'Shukriya! Phir tashreef laiye.',
        'timezone' => ShopTimezone::AsiaKarachi,
    ]);

    $this->get('/app/shop/settings')->assertInertia(fn (Assert $assert): Assert => $assert->where('shop.name', 'Bismillah Karyana Store'));
});

test('clearing optional details stores them as empty', function (): void {
    $user = shopWithOwner();

    $this->actingAs($user)
        ->put('/app/shop/settings', ['name' => 'Corner Shop', 'phone' => '', 'address' => '   ', 'receipt_footer' => null])
        ->assertSessionHasNoErrors();

    expect($user->shop()->firstOrFail()->only(['phone', 'address', 'receipt_footer']))->toBe([
        'phone' => null,
        'address' => null,
        'receipt_footer' => null,
    ]);
});

test('changes are written to the audit log, and an unchanged save writes nothing', function (): void {
    $user = shopWithOwner();
    $mock = Mockery::spy();
    Log::shouldReceive('channel')->with('audit')->andReturn($mock);

    $this->actingAs($user)->put('/app/shop/settings', shopSettingsPayload(['phone' => '042-35761234', 'address' => 'Old address', 'receipt_footer' => null]));
    $this->actingAs($user)->put('/app/shop/settings', shopSettingsPayload(['phone' => '042-35761234', 'address' => 'Old address', 'receipt_footer' => null]));

    $mock->shouldHaveReceived('info')->once()->with('audit.shop_settings_updated', [
        'shop_id' => $user->shop_id,
        'actor_id' => $user->id,
        'changes' => ['name' => ['before' => 'Original Store', 'after' => 'Bismillah Karyana Store']],
    ]);
});

test('invalid shop details are rejected without changing the shop', function (array $overrides, string $field, string $message): void {
    $user = shopWithOwner();

    $this->actingAs($user)
        ->from('/app/shop/settings')
        ->put('/app/shop/settings', shopSettingsPayload($overrides))
        ->assertRedirect('/app/shop/settings')
        ->assertSessionHasErrors([$field => $message]);

    expect($user->shop()->firstOrFail()->only(['name', 'phone', 'address', 'receipt_footer']))->toBe([
        'name' => 'Original Store',
        'phone' => '042-35761234',
        'address' => 'Old address',
        'receipt_footer' => null,
    ]);
})->with([
    'missing name' => [['name' => null], 'name', 'Enter the shop name.'],
    'blank name' => [['name' => '    '], 'name', 'Enter the shop name.'],
    'name too long' => [['name' => str_repeat('a', 121)], 'name', 'The shop name field must not be greater than 120 characters.'],
    'name not text' => [['name' => ['Store']], 'name', 'The shop name field must be a string.'],
    'phone with letters' => [['phone' => '0300-CALL-NOW'], 'phone', 'Use digits with optional spaces or dashes, for example 0300-1234567 or +92 300 1234567.'],
    'phone with doubled separators' => [['phone' => '0300--1234567'], 'phone', 'Use digits with optional spaces or dashes, for example 0300-1234567 or +92 300 1234567.'],
    'phone too short' => [['phone' => '12345'], 'phone', 'The phone field must be at least 7 characters.'],
    'phone too long' => [['phone' => '0300 1234 5678 9012 3'], 'phone', 'The phone field must not be greater than 20 characters.'],
    'address too long' => [['address' => str_repeat('a', 256)], 'address', 'The address field must not be greater than 255 characters.'],
    'footer too long' => [['receipt_footer' => str_repeat('a', 256)], 'receipt_footer', 'The receipt footer field must not be greater than 255 characters.'],
]);

test('staff cannot open or change shop settings', function (string $method): void {
    $user = shopWithOwner();
    $staff = User::factory()->create(['shop_id' => $user->shop_id]);

    $this->actingAs($staff)
        ->call($method, '/app/shop/settings', shopSettingsPayload(['name' => 'Staff Rename']))
        ->assertForbidden();

    expect($user->shop()->firstOrFail()->name)->toBe('Original Store');
})->with(['GET', 'PUT']);

test('a browser-supplied shop id or timezone cannot redirect the update', function (): void {
    $user = shopWithOwner('Victim Store');
    $attacker = shopWithOwner('Attacker Store');

    $this->actingAs($attacker)
        ->put('/app/shop/settings', shopSettingsPayload([
            'name' => 'Renamed',
            'shop_id' => $user->shop_id,
            'id' => $user->shop_id,
            'timezone' => 'Asia/Dubai',
        ]))
        ->assertSessionHasNoErrors();

    expect(Shop::query()->findOrFail($user->shop_id)->name)->toBe('Victim Store')
        ->and(Shop::query()->findOrFail($attacker->shop_id)->only(['name', 'timezone']))->toBe(['name' => 'Renamed', 'timezone' => ShopTimezone::AsiaKarachi]);
});

test('owners only ever see their own shop', function (): void {
    shopWithOwner('Victim Store');
    $user = shopWithOwner('Attacker Store');

    $this->actingAs($user)
        ->get('/app/shop/settings?shop_id=1&shop=1')
        ->assertInertia(fn (Assert $assert): Assert => $assert->where('settings.name', 'Attacker Store'));
});

test('owners who may not sign in cannot reach shop settings', function (User $user): void {
    $this->actingAs($user)->get('/app/shop/settings')->assertRedirect('/auth/login');

    $this->assertGuest();
})->with([
    'deactivated owner' => fn (): User => User::factory()->owner()->deactivated()->create(),
    'owner without a shop' => fn (): User => User::factory()->owner()->withoutShop()->create(),
]);

test('the manage-shop gate requires an active owner with a shop', function (): void {
    expect(User::factory()->owner()->create()->can('manage-shop'))->toBeTrue()
        ->and(User::factory()->create()->can('manage-shop'))->toBeFalse()
        ->and(User::factory()->owner()->deactivated()->create()->can('manage-shop'))->toBeFalse()
        ->and(User::factory()->owner()->withoutShop()->create()->can('manage-shop'))->toBeFalse();
});

test('shops cannot be deleted', function (): void {
    $user = shopWithOwner();

    $this->actingAs($user)->delete('/app/shop/settings')->assertMethodNotAllowed();

    expect(Shop::query()->count())->toBe(1);
});
