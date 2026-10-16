<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Shared\Enums\SharedRealtimeChannel;
use App\Modules\Shared\Realtime\Support\ChannelPatternResolver;
use App\Modules\Users\Enums\UsersRealtimeChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('broadcasting.default', 'reverb');
    config()->set('broadcasting.connections.reverb.key', 'test-key');
    config()->set('broadcasting.connections.reverb.secret', 'test-secret');
    config()->set('broadcasting.connections.reverb.app_id', 'test-app');
    config()->set('broadcasting.connections.reverb.options.host', '127.0.0.1');
    config()->set('broadcasting.connections.reverb.options.port', 8080);
    config()->set('broadcasting.connections.reverb.options.scheme', 'http');
    config()->set('broadcasting.connections.reverb.options.useTLS', false);

    Broadcast::forgetDrivers();
    require base_path('routes/channels.php');
});
test('guests cannot authorize private broadcast channels', function (): void {
    $this->postJson('/broadcasting/auth', [
        'channel_name' => 'private-users.index',
        'socket_id' => '1234.5678',
    ])->assertForbidden();
});

test('every generated channel contract has backend authorization and matching notification routing', function (): void {
    $registeredChannels = Broadcast::getChannels();
    foreach ([...UsersRealtimeChannel::cases(), ...SharedRealtimeChannel::cases()] as $channel) {
        expect($registeredChannels)->toHaveKey($channel->value);
    }

    $user = User::factory()->create();
    expect($user->receivesBroadcastNotificationsOn())->toBe(
        ChannelPatternResolver::resolve(SharedRealtimeChannel::UserNotifications->value, ['userId' => $user->id]),
    );
});
test('non admin users cannot authorize admin private channels', function (): void {
    $user = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($user)->postJson('/broadcasting/auth', [
        'channel_name' => 'private-users.index',
        'socket_id' => '1234.5678',
    ])->assertForbidden();
});
test('admin users can authorize private channels via web session', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->post('/broadcasting/auth', [
        'channel_name' => 'private-users.index',
        'socket_id' => '1234.5678',
    ])->assertOk()->assertSee('auth');
});
test('admin users can authorize presence channels via web session', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->post('/broadcasting/auth', [
        'channel_name' => 'presence-users.index.presence',
        'socket_id' => '1234.5678',
    ])->assertOk()->assertSee('auth')->assertSee('channel_data');
});
test('admin users can authorize private channels via sanctum', function (): void {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    Sanctum::actingAs($admin);

    $this->post('/api/broadcasting/auth', [
        'channel_name' => 'private-users.index',
        'socket_id' => '1234.5678',
    ])->assertOk()->assertSee('auth');
});
test('non admin users cannot authorize private channels via sanctum', function (): void {
    $user = User::factory()->create(['role' => UserRole::User]);

    Sanctum::actingAs($user);

    $this->postJson('/api/broadcasting/auth', [
        'channel_name' => 'private-users.index',
        'socket_id' => '1234.5678',
    ])->assertForbidden();
});
