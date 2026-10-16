<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Shop;
use App\Models\User;
use App\Modules\Users\Enums\UsersRealtimeChannel;
use App\Modules\Users\Events\Broadcast\UserChanged;
use App\Modules\Users\Events\Broadcast\UsersListChanged;
use App\Modules\Users\Notifications\UserManagementBroadcastNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('creating a user dispatches realtime events and notifications', function (): void {
    Event::fake([UsersListChanged::class, UserChanged::class]);
    Notification::fake();

    $admin = User::factory()->owner()->create();
    $otherAdmin = User::factory()->owner()->create();

    $this->actingAs($admin)
        ->withHeader('X-Socket-ID', '1234.5678')
        ->post('/app/admin/users', [
            'name' => 'Realtime User',
            'email' => 'realtime-user@example.com',
            'password' => 'Password123!@#',
            'password_confirmation' => 'Password123!@#',
            'role' => UserRole::User->value,
        ])
        ->assertRedirect('/app/admin/users');

    Event::assertDispatched(UsersListChanged::class, static function (UsersListChanged $usersListChanged) use ($admin): true {
        expect($usersListChanged->broadcastAs())->toBe('users.list.changed')
            ->and((string) ($usersListChanged->broadcastOn()[0] ?? ''))->toBe('private-'.UsersRealtimeChannel::Index->value)
            ->and($usersListChanged->broadcastWith())->toMatchArray([
                'action' => 'create',
                'actorUserId' => $admin->id,
            ]);

        return true;
    });

    Event::assertDispatched(UserChanged::class, static function (UserChanged $userChanged): true {
        $payload = $userChanged->broadcastWith();

        expect($userChanged->broadcastAs())->toBe('users.user.changed')
            ->and($payload)->toMatchArray(['action' => 'create'])
            ->and(data_get($payload, 'user.email'))->toBe('realtime-user@example.com');

        return true;
    });

    Notification::assertSentTo($otherAdmin, UserManagementBroadcastNotification::class);
});
test('updating a user dispatches realtime events', function (): void {
    Event::fake([UsersListChanged::class, UserChanged::class]);

    $admin = User::factory()->owner()->create();
    $target = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($admin)
        ->withHeader('X-Socket-ID', '1234.5678')
        ->put('/app/admin/users/'.$target->id, [
            'name' => 'Realtime Updated',
            'email' => 'realtime-updated@example.com',
            'password' => '',
            'role' => UserRole::Admin->value,
        ])
        ->assertRedirect('/app/admin/users');

    Event::assertDispatched(UsersListChanged::class, static function (UsersListChanged $usersListChanged): true {
        expect($usersListChanged->broadcastWith())->toMatchArray(['action' => 'update']);

        return true;
    });
    Event::assertDispatched(UserChanged::class, static function (UserChanged $userChanged): true {
        expect($userChanged->broadcastWith())->toMatchArray(['action' => 'update']);

        return true;
    });
});
test('deactivating and reactivating a user dispatch realtime events', function (): void {
    Event::fake([UsersListChanged::class, UserChanged::class]);

    $admin = User::factory()->owner()->create();
    $target = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($admin)
        ->withHeader('X-Socket-ID', '1234.5678')
        ->patch('/app/admin/users/'.$target->id.'/deactivate')
        ->assertRedirect('/app/admin/users');

    $this->actingAs($admin)
        ->patch('/app/admin/users/'.$target->id.'/reactivate')
        ->assertRedirect('/app/admin/users');

    Event::assertDispatched(UsersListChanged::class, static fn (UsersListChanged $usersListChanged): bool => data_get($usersListChanged->broadcastWith(), 'action') === 'deactivate');
    Event::assertDispatched(UserChanged::class, static fn (UserChanged $userChanged): bool => data_get($userChanged->broadcastWith(), 'action') === 'deactivate'
        && data_get($userChanged->broadcastWith(), 'user.deactivated_at') !== null);
    Event::assertDispatched(UserChanged::class, static fn (UserChanged $userChanged): bool => data_get($userChanged->broadcastWith(), 'action') === 'reactivate'
        && data_get($userChanged->broadcastWith(), 'user.deactivated_at') === null);
});

test('management notifications only reach active owners of the same shop', function (): void {
    Notification::fake();

    $admin = User::factory()->owner()->create();
    $sameShopOwner = User::factory()->owner()->create();
    $deactivatedOwner = User::factory()->owner()->deactivated()->create();
    $staff = User::factory()->create();
    $otherShopOwner = User::factory()->owner()->create(['shop_id' => Shop::factory()->create()->id]);

    $this->actingAs($admin)
        ->patch('/app/admin/users/'.$staff->id.'/deactivate')
        ->assertRedirect('/app/admin/users');

    Notification::assertSentTo($sameShopOwner, UserManagementBroadcastNotification::class);
    Notification::assertNotSentTo([$admin, $deactivatedOwner, $staff, $otherShopOwner], UserManagementBroadcastNotification::class);
});
