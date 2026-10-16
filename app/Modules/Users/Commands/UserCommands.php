<?php

declare(strict_types=1);

namespace App\Modules\Users\Commands;

use App\Models\User;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use BackedEnum;
use RuntimeException;
use Stringable;

final class UserCommands
{
    public function create(CreateUserData $createUserData): UserCommandResult
    {
        $user = User::create([
            'name' => $createUserData->name,
            'email' => $createUserData->email,
            'role' => $createUserData->role,
            'password' => $createUserData->password,
        ]);

        if (! $user->exists) {
            throw new RuntimeException('User creation was cancelled.');
        }

        return new UserCommandResult(user: $user);
    }

    public function update(User $user, UpdateUserData $updateUserData): UserCommandResult
    {
        $before = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ];

        $user->name = $updateUserData->name;
        $user->email = $updateUserData->email;
        $user->role = $updateUserData->role;

        $passwordChanged = $updateUserData->password !== null && $updateUserData->password !== '';

        if ($passwordChanged) {
            $user->password = $updateUserData->password;
        }

        if (! $user->save()) {
            throw new RuntimeException('User update was cancelled.');
        }

        return new UserCommandResult(
            user: $user,
            changes: $this->computeChanges($before, $user, $passwordChanged),
        );
    }

    public function delete(User $user): void
    {
        if ($user->delete() !== true) {
            throw new RuntimeException('User deletion was cancelled.');
        }
    }

    /**
     * @param  array{name: mixed, email: mixed, role: mixed}  $before
     * @return array<string, array<string, string>>
     */
    private function computeChanges(array $before, User $user, bool $passwordChanged): array
    {
        $changes = [];

        foreach (['name', 'email', 'role'] as $key) {
            $beforeValue = $this->auditValue($before[$key]);
            $afterValue = $this->auditValue($user->{$key});

            if ($beforeValue !== $afterValue) {
                $changes[$key] = [
                    'before' => $beforeValue,
                    'after' => $afterValue,
                ];
            }
        }

        if ($passwordChanged) {
            $changes['password'] = [
                'before' => '[REDACTED]',
                'after' => '[REDACTED]',
            ];
        }

        return $changes;
    }

    private function auditValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        if ($value instanceof Stringable) {
            return $value->__toString();
        }

        return match (true) {
            is_string($value) => $value,
            is_int($value), is_float($value) => (string) $value,
            is_bool($value) => $value ? 'true' : 'false',
            $value === null => '',
            is_array($value) => $this->encodeAuditArray($value),
            is_object($value) => '[object '.$value::class.']',
            is_resource($value) => '[resource '.get_resource_type($value).']',
            default => '[unknown]',
        };
    }

    /**
     * @param  array<mixed>  $value
     */
    private function encodeAuditArray(array $value): string
    {
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return is_string($encoded) ? $encoded : '[unserializable array]';
    }
}
