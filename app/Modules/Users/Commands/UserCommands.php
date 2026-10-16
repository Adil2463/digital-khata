<?php

declare(strict_types=1);

namespace App\Modules\Users\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Modules\Shared\Auth\CredentialRotator;
use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Data\UpdateUserData;
use BackedEnum;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Stringable;

final readonly class UserCommands
{
    public function __construct(
        private CredentialRotator $credentialRotator,
    ) {}

    /**
     * The new account joins the owner's shop and is verified: the owner vouches for it,
     * and no verification email could be delivered anyway.
     */
    public function create(CreateUserData $createUserData, User $owner): UserCommandResult
    {
        if ($owner->shop_id === null) {
            throw new RuntimeException('Only an owner who belongs to a shop can create accounts.');
        }

        $user = new User();
        $user->forceFill([
            'name' => $createUserData->name,
            'email' => $createUserData->email,
            'role' => $createUserData->role,
            'password' => $createUserData->password,
            'shop_id' => $owner->shop_id,
            'email_verified_at' => CarbonImmutable::now(),
        ]);

        if (! $user->save()) {
            throw new RuntimeException('User creation was cancelled.');
        }

        return new UserCommandResult(user: $user);
    }

    /**
     * A changed login or password signs the account out everywhere, except the actor's own
     * current session when they edit themselves.
     *
     * @throws ValidationException
     */
    public function update(User $user, UpdateUserData $updateUserData, User $actor, ?string $actorSessionId = null): UserCommandResult
    {
        return DB::transaction(function () use ($user, $updateUserData, $actor, $actorSessionId): UserCommandResult {
            if ($user->isOwner() && $updateUserData->role !== UserRole::Admin) {
                $this->ensureOwnerRemains($user, $actor, 'role', 'You cannot remove your own Owner role.');
            }

            $before = [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ];

            $user->name = $updateUserData->name;
            $user->email = $updateUserData->email;
            $user->role = $updateUserData->role;
            $user->email_verified_at ??= CarbonImmutable::now();

            $loginChanged = $user->isDirty('email');
            $passwordChanged = $updateUserData->password !== null && $updateUserData->password !== '';

            if ($passwordChanged) {
                $user->password = $updateUserData->password;
            }

            if (! $user->save()) {
                throw new RuntimeException('User update was cancelled.');
            }

            if ($loginChanged || $passwordChanged) {
                $this->credentialRotator->revokeAccess($user, $user->is($actor) ? $actorSessionId : null);
            }

            return new UserCommandResult(
                user: $user,
                changes: $this->computeChanges($before, $user, $passwordChanged),
            );
        });
    }

    /**
     * @throws ValidationException
     */
    public function deactivate(User $user, User $actor): UserCommandResult
    {
        return DB::transaction(function () use ($user, $actor): UserCommandResult {
            if ($user->isOwner()) {
                $this->ensureOwnerRemains($user, $actor, 'account', 'You cannot deactivate your own account.');
            }

            if ($user->is($actor)) {
                throw ValidationException::withMessages(['account' => 'You cannot deactivate your own account.']);
            }

            $user->deactivated_at = CarbonImmutable::now();

            if (! $user->save()) {
                throw new RuntimeException('User deactivation was cancelled.');
            }

            $this->credentialRotator->revokeAccess($user);

            return new UserCommandResult(
                user: $user,
                changes: ['status' => ['before' => 'active', 'after' => 'deactivated']],
            );
        });
    }

    public function reactivate(User $user): UserCommandResult
    {
        $user->deactivated_at = null;

        if (! $user->save()) {
            throw new RuntimeException('User reactivation was cancelled.');
        }

        return new UserCommandResult(
            user: $user,
            changes: ['status' => ['before' => 'deactivated', 'after' => 'active']],
        );
    }

    /**
     * Locks the shop's owner rows so two owners demoting or deactivating each other at the
     * same time cannot leave the shop without an active owner.
     *
     * @throws ValidationException
     */
    private function ensureOwnerRemains(User $owner, User $actor, string $field, string $selfMessage): void
    {
        if ($owner->is($actor)) {
            throw ValidationException::withMessages([$field => $selfMessage]);
        }

        $otherActiveOwnerIds = User::query()
            ->where('shop_id', $owner->shop_id)
            ->where('role', UserRole::Admin)
            ->whereNull('deactivated_at')
            ->whereKeyNot($owner->id)
            ->lockForUpdate()
            ->pluck('id');

        if ($otherActiveOwnerIds->isEmpty()) {
            throw ValidationException::withMessages([$field => 'The shop must keep at least one active owner.']);
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
