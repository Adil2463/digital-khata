<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Modules\Shared\Realtime\Support\ChannelPatternResolver;

test('it resolves channel patterns with scalar parameters', function (): void {
    $resolved = ChannelPatternResolver::resolve('users.{userId}.notifications', [
        'userId' => 42,
    ]);

    expect($resolved)->toBe('users.42.notifications');
});

test('it resolves channel patterns with enum, boolean, and stringable parameters', function (): void {
    $stringable = new class implements Stringable
    {
        public function __toString(): string
        {
            return 'stringable-param';
        }
    };

    $resolved = ChannelPatternResolver::resolve('teams.{role}.active.{flag}.{sub}', [
        'role' => UserRole::Admin,
        'flag' => true,
        'sub' => $stringable,
    ]);

    expect($resolved)->toBe('teams.admin.active.true.stringable-param');
});

test('it throws when a parameter is missing', function (): void {
    expect(
        fn (): string => ChannelPatternResolver::resolve('users.{userId}'),
    )->toThrow(InvalidArgumentException::class, 'Missing channel parameter "userId".');
});
