<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use App\Modules\Shared\Auth\DemoAccounts;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

uses(RefreshDatabase::class);

const OWNER_PASSWORD = 'Str0ng-Owner-Pass';

function installShop(TestCase $testCase, string $ownerEmail = 'owner@shop.local', string $password = OWNER_PASSWORD, ?string $confirmation = null): PendingCommand
{
    return $testCase->pendingArtisan('khata:install', [
        '--shop-name' => 'Bismillah Karyana Store',
        '--owner-name' => 'Muhammad Aslam',
        '--owner-email' => $ownerEmail,
    ])
        ->expectsQuestion('Owner password', $password)
        ->expectsQuestion('Confirm owner password', $confirmation ?? $password);
}

/**
 * @param  array{name: string, email: string, role: UserRole, password: string}  $credential
 */
function seedPublishedDemoAccount(array $credential): User
{
    return User::factory()->create([
        'name' => $credential['name'],
        'email' => $credential['email'],
        'role' => $credential['role'],
        'password' => $credential['password'],
    ]);
}

test('it creates the shop and a verified owner without printing the password', function (): void {
    installShop($this)
        ->doesntExpectOutputToContain(OWNER_PASSWORD)
        ->expectsOutputToContain('Digital Khata is installed.')
        ->assertSuccessful();

    $shop = Shop::query()->sole();
    $user = User::query()->where('email', 'owner@shop.local')->sole();

    expect($shop->name)->toBe('Bismillah Karyana Store')
        ->and($shop->timezone)->toBe(Shop::DEFAULT_TIMEZONE)
        ->and($user->role)->toBe(UserRole::Admin)
        ->and($user->shop_id)->toBe($shop->id)
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->password)->not->toBe(OWNER_PASSWORD)
        ->and(Hash::check(OWNER_PASSWORD, $user->password))->toBeTrue();
});

test('the installed owner can log in and open the dashboard', function (): void {
    installShop($this)->assertSuccessful();

    $this->post('/auth/login', ['email' => 'owner@shop.local', 'password' => OWNER_PASSWORD])
        ->assertRedirect(route('app.dashboard', absolute: false));

    $this->get('/app/dashboard')->assertOk();
});

test('it normalizes the owner email and honours a custom timezone', function (): void {
    $this->pendingArtisan('khata:install', [
        '--shop-name' => '  Corner Shop  ',
        '--owner-name' => 'Owner',
        '--owner-email' => '  Owner@Shop.Local ',
        '--timezone' => 'Asia/Dubai',
    ])
        ->expectsQuestion('Owner password', OWNER_PASSWORD)
        ->expectsQuestion('Confirm owner password', OWNER_PASSWORD)
        ->assertSuccessful();

    expect(Shop::query()->sole()->only(['name', 'timezone']))->toBe(['name' => 'Corner Shop', 'timezone' => 'Asia/Dubai'])
        ->and(User::query()->where('email', 'owner@shop.local')->exists())->toBeTrue();
});

test('it prompts for missing details', function (): void {
    $this->pendingArtisan('khata:install')
        ->expectsQuestion('Shop name', 'Prompted Store')
        ->expectsQuestion('Owner name', 'Prompted Owner')
        ->expectsQuestion('Owner login email', 'prompted@shop.local')
        ->expectsQuestion('Owner password', OWNER_PASSWORD)
        ->expectsQuestion('Confirm owner password', OWNER_PASSWORD)
        ->assertSuccessful();

    expect(Shop::query()->sole()->name)->toBe('Prompted Store');
});

test('it refuses to run twice and changes nothing', function (): void {
    installShop($this)->assertSuccessful();

    $this->pendingArtisan('khata:install', ['--shop-name' => 'Second', '--owner-name' => 'X', '--owner-email' => 'second@shop.local'])
        ->expectsOutputToContain('already installed')
        ->assertFailed();

    expect(Shop::query()->count())->toBe(1)
        ->and(User::query()->where('email', 'second@shop.local')->exists())->toBeFalse();
});

test('it refuses to run without an interactive password prompt', function (): void {
    $this->pendingArtisan('khata:install', [
        '--shop-name' => 'Shop',
        '--owner-name' => 'Owner',
        '--owner-email' => 'owner@shop.local',
        '--no-interaction' => true,
    ])->assertFailed();

    expect(Shop::query()->exists())->toBeFalse()
        ->and(User::query()->exists())->toBeFalse();
});

test('it rejects invalid input without creating anything', function (string $ownerEmail, string $password, string $confirmation, ?string $timezone, string $message): void {
    User::factory()->create(['email' => 'taken@shop.local']);

    $this->pendingArtisan('khata:install', [
        '--shop-name' => 'Shop',
        '--owner-name' => 'Owner',
        '--owner-email' => $ownerEmail,
        ...($timezone === null ? [] : ['--timezone' => $timezone]),
    ])
        ->expectsQuestion('Owner password', $password)
        ->expectsQuestion('Confirm owner password', $confirmation)
        ->expectsOutputToContain($message)
        ->assertFailed();

    expect(Shop::query()->exists())->toBeFalse()
        ->and(User::query()->count())->toBe(1);
})->with([
    'short password' => ['owner@shop.local', 'short', 'short', null, 'at least 8 characters'],
    'mismatched confirmation' => ['owner@shop.local', OWNER_PASSWORD, 'Different-Pass-1', null, 'confirmation does not match'],
    'login already used' => ['taken@shop.local', OWNER_PASSWORD, OWNER_PASSWORD, null, 'already been taken'],
    'not an email' => ['owner', OWNER_PASSWORD, OWNER_PASSWORD, null, 'valid email address'],
    'unknown timezone' => ['owner@shop.local', OWNER_PASSWORD, OWNER_PASSWORD, 'Mars/Olympus', 'valid timezone'],
]);

test('it locks demo accounts that still use published passwords without deleting them', function (): void {
    config(['session.driver' => 'database']);
    $admin = seedPublishedDemoAccount(DemoAccounts::CREDENTIALS[0]);
    $user = seedPublishedDemoAccount(DemoAccounts::CREDENTIALS[1]);
    $admin->createToken('demo-token');
    DB::table('sessions')->insert([
        'id' => 'demo-session',
        'user_id' => $user->id,
        'payload' => '',
        'last_activity' => now()->getTimestamp(),
    ]);
    $rememberToken = $admin->remember_token;

    installShop($this)
        ->expectsOutputToContain('admin@app.com')
        ->expectsConfirmation('Lock them? Each gets a random unknown password and loses its sessions and API tokens; no data is deleted.', 'yes')
        ->assertSuccessful();

    $admin->refresh();
    $user->refresh();

    expect(DemoAccounts::usersWithPublishedPasswords())->toBe([])
        ->and(Hash::check(DemoAccounts::CREDENTIALS[0]['password'], $admin->password))->toBeFalse()
        ->and(Hash::check(DemoAccounts::CREDENTIALS[1]['password'], $user->password))->toBeFalse()
        ->and($admin->remember_token)->not->toBe($rememberToken)
        ->and($admin->tokens()->count())->toBe(0)
        ->and(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse()
        ->and($admin->role)->toBe(UserRole::Admin)
        ->and($admin->shop_id)->toBe(Shop::query()->sole()->id);

    $this->post('/auth/login', ['email' => 'admin@app.com', 'password' => DemoAccounts::CREDENTIALS[0]['password']]);
    $this->assertGuest();
});

test('declining to lock demo accounts cancels installation and changes nothing', function (): void {
    $user = seedPublishedDemoAccount(DemoAccounts::CREDENTIALS[0]);

    installShop($this)
        ->expectsConfirmation('Lock them? Each gets a random unknown password and loses its sessions and API tokens; no data is deleted.', 'no')
        ->expectsOutputToContain('Installation cancelled')
        ->assertFailed();

    expect(Shop::query()->exists())->toBeFalse()
        ->and(Hash::check(DemoAccounts::CREDENTIALS[0]['password'], $user->refresh()->password))->toBeTrue()
        ->and($user->shop_id)->toBeNull();
});

test('demo emails with changed passwords are left untouched', function (): void {
    $admin = User::factory()->create(['email' => 'admin@app.com', 'password' => 'Owner-Changed-It-1']);

    installShop($this)->assertSuccessful();

    expect(Hash::check('Owner-Changed-It-1', $admin->refresh()->password))->toBeTrue();
});

test('existing accounts are attached to the new shop', function (): void {
    $existing = User::factory()->count(2)->create();

    installShop($this)
        ->expectsOutputToContain('Existing accounts attached to the shop')
        ->assertSuccessful();

    $shopId = Shop::query()->sole()->id;

    expect($existing->map(fn (User $user): ?int => $user->refresh()->shop_id)->all())->toBe([$shopId, $shopId]);
});

test('the shop cannot be deleted while users belong to it', function (): void {
    installShop($this)->assertSuccessful();

    expect(fn (): ?bool => Shop::query()->sole()->delete())->toThrow(QueryException::class);
});
