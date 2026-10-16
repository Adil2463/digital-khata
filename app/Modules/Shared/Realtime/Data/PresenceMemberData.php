<?php

declare(strict_types=1);

namespace App\Modules\Shared\Realtime\Data;

use App\Enums\UserRole;
use App\Models\User;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class PresenceMemberData extends Data
{
    public function __construct(
        public int $id,
        public string $name,
        public UserRole $role,
    ) {}

    public static function fromUser(User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            role: $user->role,
        );
    }
}
