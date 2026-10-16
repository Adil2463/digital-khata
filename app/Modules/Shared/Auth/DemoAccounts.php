<?php

declare(strict_types=1);

namespace App\Modules\Shared\Auth;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class DemoAccounts
{
    /**
     * These credentials are published with the starter kit, so an account that
     * still accepts one of them is effectively public and must never guard shop data.
     *
     * @var list<array{name: string, email: string, role: UserRole, password: string}>
     */
    public const array CREDENTIALS = [
        ['name' => 'Admin', 'email' => 'admin@app.com', 'role' => UserRole::Admin, 'password' => 'Admin123!@#'],
        ['name' => 'User', 'email' => 'user@app.com', 'role' => UserRole::User, 'password' => 'User123!@#'],
    ];

    /**
     * @return list<User>
     */
    public static function usersWithPublishedPasswords(): array
    {
        $users = [];

        foreach (self::CREDENTIALS as $credential) {
            $user = User::query()->where('email', $credential['email'])->first();

            if ($user instanceof User && Hash::check($credential['password'], $user->password)) {
                $users[] = $user;
            }
        }

        return $users;
    }
}
