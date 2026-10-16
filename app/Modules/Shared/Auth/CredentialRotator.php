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
     * Replaces the password and revokes every credential issued under the old one:
     * remember-me cookies, API tokens, and database-backed sessions.
     */
    public function replacePassword(User $user, string $plainPassword): void
    {
        $user->forceFill([
            'password' => $plainPassword,
            'remember_token' => Str::random(60),
        ]);

        if (! $user->save()) {
            throw new RuntimeException('Password update was cancelled.');
        }

        $user->tokens()->delete();

        // Other session drivers cannot be enumerated per user; their sessions expire with the lifetime.
        if (config()->string('session.driver') === 'database') {
            $connection = config('session.connection');

            DB::connection(is_string($connection) ? $connection : null)
                ->table(config()->string('session.table'))
                ->where('user_id', $user->id)
                ->delete();
        }
    }

    public function lock(User $user): void
    {
        $this->replacePassword($user, Str::random(64));
    }
}
