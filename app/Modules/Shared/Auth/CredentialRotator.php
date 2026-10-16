<?php

declare(strict_types=1);

namespace App\Modules\Shared\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class CredentialRotator
{
    /**
     * Replaces the password and revokes every credential issued under the old one.
     * `$keptSessionId` lets a user who changes their own password stay signed in on this device.
     */
    public function replacePassword(User $user, string $plainPassword, ?string $keptSessionId = null): void
    {
        $user->forceFill(['password' => $plainPassword]);

        if (! $user->save()) {
            throw new RuntimeException('Password update was cancelled.');
        }

        $this->revokeAccess($user, $keptSessionId);
    }

    /**
     * Revokes remember-me cookies, API tokens, and database-backed sessions.
     * Other session drivers cannot be enumerated per user; `EnsureAccountCanSignIn`
     * still ends deactivated sessions on their next request.
     */
    public function revokeAccess(User $user, ?string $keptSessionId = null): void
    {
        $user->forceFill(['remember_token' => Str::random(60)]);

        if (! $user->save()) {
            throw new RuntimeException('Credential revocation was cancelled.');
        }

        $user->tokens()->delete();

        if (config()->string('session.driver') !== 'database') {
            return;
        }

        $connection = config('session.connection');

        $builder = DB::connection(is_string($connection) ? $connection : null)
            ->table(config()->string('session.table'))
            ->where('user_id', $user->id);

        if ($keptSessionId !== null) {
            $builder->where('id', '!=', $keptSessionId);
        }

        $builder->delete();
    }

    public function lock(User $user): void
    {
        $this->replacePassword($user, Str::random(64));
    }
}
