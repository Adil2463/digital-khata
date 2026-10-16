<?php

declare(strict_types=1);

use App\Enums\UserRole;
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

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $otherAdmin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)
        ->withHeader('X-Socket-ID', '1234.5678')
        ->post('/app/admin/users', [
            'name' => 'Realtime User',
            'email' => 'realtime-user@example.com',
            'password' => 'Password123!@#',
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

    $admin = User::factory()->create(['role' => UserRole::Admin]);
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
test('deleting a user dispatches realtime events', function (): void {
    Event::fake([UsersListChanged::class, UserChanged::class]);

    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $target = User::factory()->create(['role' => UserRole::User]);

    $this->actingAs($admin)
        ->withHeader('X-Socket-ID', '1234.5678')
        ->delete('/app/admin/users/'.$target->id)
        ->assertRedirect('/app/admin/users');

    Event::assertDispatched(UsersListChanged::class, static function (UsersListChanged $usersListChanged): true {
        expect($usersListChanged->broadcastWith())->toMatchArray(['action' => 'delete']);

        return true;
    });
    Event::assertDispatched(UserChanged::class, static function (UserChanged $userChanged): true {
        expect($userChanged->broadcastWith())->toMatchArray(['action' => 'delete', 'user' => null]);

        return true;
    });
});
