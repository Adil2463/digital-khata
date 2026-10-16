<?php

declare(strict_types=1);

namespace App\Modules\Shared\Data;

use App\Enums\UserRole;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rules\Enum;
use Spatie\LaravelData\Attributes\Validation\Rule;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\EnumCast;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class UserViewData extends Data
{
    public function __construct(
        public int $id,

        #[Rule(['required', 'string'])]
        public string $name,

        #[Rule(['required', 'string', 'email', 'max:255'])]
        public string $email,

        #[Rule(['required', 'string', new Enum(UserRole::class)])]
        #[WithCast(EnumCast::class, type: UserRole::class)]
        public UserRole $role,

        public CarbonImmutable $created_at,

        public CarbonImmutable $updated_at,

        #[Rule(['nullable', 'date'])]
        public ?CarbonImmutable $email_verified_at = null,
    ) {}

    public static function fromModel(User $user): self
    {
        $emailVerifiedAt = $user->email_verified_at;

        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            role: $user->role,
            created_at: CarbonImmutable::parse($user->created_at),
            updated_at: CarbonImmutable::parse($user->updated_at),
            email_verified_at: $emailVerifiedAt === null ? null : CarbonImmutable::instance($emailVerifiedAt),
        );
    }
}
